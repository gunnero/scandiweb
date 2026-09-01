<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Bootstrap\ApplicationFactory;
use App\Infrastructure\Database\ConnectionFactory;
use GraphQL\GraphQL;
use PDO;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class GraphQLOrderMutationTest extends TestCase
{
    public function testCreateOrderMutationPersistsTheCompleteOrder(): void
    {
        $connection = ConnectionFactory::create();
        $result = GraphQL::executeQuery(
            ApplicationFactory::schema($connection),
            <<<'GRAPHQL'
                mutation CreateOrder($items: [OrderItemInput!]!) {
                    createOrder(items: $items) {
                        id
                        total
                        status
                        createdAt
                    }
                }
                GRAPHQL,
            null,
            null,
            [
                'items' => [
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
                ],
            ]
        )->toArray();

        self::assertArrayNotHasKey('errors', $result);
        $order = $result['data']['createOrder'];

        try {
            self::assertSame('pending', $order['status']);
            self::assertSame(3496.63, $order['total']);
            self::assertNotSame('', $order['createdAt']);
            self::assertSame(
                ['status' => 'pending', 'total' => '3496.63', 'currency_label' => 'USD'],
                $this->orderHeader($connection, $order['id'])
            );
            self::assertSame(
                [
                    [
                        'product_public_id' => 'apple-airtag',
                        'quantity' => 1,
                        'unit_price' => '120.57',
                    ],
                    [
                        'product_public_id' => 'apple-imac-2021',
                        'quantity' => 2,
                        'unit_price' => '1688.03',
                    ],
                ],
                $this->orderLines($connection, $order['id'])
            );
            self::assertSame(
                [
                    ['attribute_id' => 'Capacity', 'item_id' => '512GB'],
                    ['attribute_id' => 'Touch ID in keyboard', 'item_id' => 'No'],
                    ['attribute_id' => 'With USB 3 ports', 'item_id' => 'Yes'],
                ],
                $this->orderSelections($connection, $order['id'])
            );
        } finally {
            $statement = $connection->prepare('DELETE FROM orders WHERE id = :id');
            $statement->execute(['id' => $order['id']]);
        }
    }

    /** @return array{status: string, total: string, currency_label: string} */
    private function orderHeader(PDO $connection, string $orderId): array
    {
        $statement = $connection->prepare(
            'SELECT status, total, currency_label FROM orders WHERE id = :order_id'
        );
        $statement->execute(['order_id' => $orderId]);
        $row = $statement->fetch();

        if (!is_array($row)) {
            throw new RuntimeException('The GraphQL order was not persisted.');
        }

        return $row;
    }

    /** @return list<array{product_public_id: string, quantity: int, unit_price: string}> */
    private function orderLines(PDO $connection, string $orderId): array
    {
        $statement = $connection->prepare(
            'SELECT product_public_id, quantity, unit_price
             FROM order_items
             WHERE order_id = :order_id
             ORDER BY product_public_id ASC'
        );
        $statement->execute(['order_id' => $orderId]);

        return $statement->fetchAll();
    }

    /** @return list<array{attribute_id: string, item_id: string}> */
    private function orderSelections(PDO $connection, string $orderId): array
    {
        $statement = $connection->prepare(
            'SELECT order_item_attributes.attribute_id, order_item_attributes.item_id
             FROM order_item_attributes
             INNER JOIN order_items ON order_items.id = order_item_attributes.order_item_id
             WHERE order_items.order_id = :order_id
             ORDER BY order_item_attributes.attribute_id ASC'
        );
        $statement->execute(['order_id' => $orderId]);

        return $statement->fetchAll();
    }
}
