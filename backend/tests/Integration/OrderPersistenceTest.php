<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Application\PlaceOrderService;
use App\Exception\UserInputException;
use App\Infrastructure\Database\ConnectionFactory;
use App\Infrastructure\Persistence\PdoAttributeRepository;
use App\Infrastructure\Persistence\PdoOrderRepository;
use App\Infrastructure\Persistence\PdoProductRepository;
use App\Infrastructure\Persistence\PdoTransactionManager;
use App\Model\Attribute\AttributeFactory;
use App\Model\Product\ProductFactory;
use PDO;
use PHPUnit\Framework\TestCase;

final class OrderPersistenceTest extends TestCase
{
    private PDO $connection;
    private PlaceOrderService $service;

    protected function setUp(): void
    {
        $this->connection = ConnectionFactory::create();
        $products = new PdoProductRepository($this->connection, new ProductFactory());
        $attributes = new PdoAttributeRepository($this->connection, new AttributeFactory());
        $this->service = new PlaceOrderService(
            $products,
            $attributes,
            new PdoOrderRepository($this->connection),
            new PdoTransactionManager($this->connection)
        );
    }

    public function testOrderStoresOneHeaderLinesAndArbitrarySelections(): void
    {
        $order = $this->service->place([
            [
                'productId' => 'apple-imac-2021',
                'quantity' => 2,
                'selectedAttributes' => [
                    ['attributeId' => 'Capacity', 'itemId' => '512GB'],
                    ['attributeId' => 'With USB 3 ports', 'itemId' => 'Yes'],
                    ['attributeId' => 'Touch ID in keyboard', 'itemId' => 'No'],
                ],
            ],
            [
                'productId' => 'apple-airtag',
                'quantity' => 1,
                'selectedAttributes' => [],
            ],
        ]);

        try {
            self::assertSame('3496.63', $order->total()->toDecimal());
            self::assertSame(2, $this->countRows('order_items', 'order_id', $order->id()));
            self::assertSame(
                3,
                $this->countSelections($order->id())
            );
        } finally {
            $statement = $this->connection->prepare('DELETE FROM orders WHERE id = :id');
            $statement->execute(['id' => $order->id()]);
        }
    }

    public function testOutOfStockProductRollsBackWithoutCreatingAnOrder(): void
    {
        $before = (int) $this->connection->query('SELECT COUNT(*) FROM orders')->fetchColumn();

        try {
            $this->service->place([
                [
                    'productId' => 'xbox-series-s',
                    'quantity' => 1,
                    'selectedAttributes' => [
                        ['attributeId' => 'Color', 'itemId' => 'Green'],
                        ['attributeId' => 'Capacity', 'itemId' => '512G'],
                    ],
                ],
            ]);
            self::fail('Expected an out-of-stock error.');
        } catch (UserInputException $error) {
            self::assertStringContainsString('out of stock', $error->getMessage());
        }

        $after = (int) $this->connection->query('SELECT COUNT(*) FROM orders')->fetchColumn();
        self::assertSame($before, $after);
    }

    private function countRows(string $table, string $column, string $id): int
    {
        $statement = $this->connection->prepare(
            sprintf('SELECT COUNT(*) FROM %s WHERE %s = :id', $table, $column)
        );
        $statement->execute(['id' => $id]);

        return (int) $statement->fetchColumn();
    }

    private function countSelections(string $orderId): int
    {
        $statement = $this->connection->prepare(
            'SELECT COUNT(*)
             FROM order_item_attributes
             INNER JOIN order_items ON order_items.id = order_item_attributes.order_item_id
             WHERE order_items.order_id = :order_id'
        );
        $statement->execute(['order_id' => $orderId]);

        return (int) $statement->fetchColumn();
    }
}
