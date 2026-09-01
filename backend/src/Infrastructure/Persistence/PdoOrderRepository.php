<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Model\Money;
use App\Model\Order\Order;
use App\Model\Order\OrderLineDraft;
use App\Repository\OrderRepositoryInterface;
use PDO;
use RuntimeException;

final class PdoOrderRepository implements OrderRepositoryInterface
{
    public function __construct(private readonly PDO $connection)
    {
    }

    public function create(Money $total, string $currencyLabel, array $lines): Order
    {
        $orderStatement = $this->connection->prepare(
            'INSERT INTO orders (status, total, currency_label) VALUES (:status, :total, :currency_label)'
        );
        $orderStatement->execute([
            'status' => 'pending',
            'total' => $total->toDecimal(),
            'currency_label' => $currencyLabel,
        ]);
        $orderId = (int) $this->connection->lastInsertId();

        foreach ($lines as $line) {
            $orderItemId = $this->insertLine($orderId, $line);
            $this->insertSelections($orderItemId, $line);
        }

        $createdAtStatement = $this->connection->prepare(
            'SELECT created_at FROM orders WHERE id = :id'
        );
        $createdAtStatement->execute(['id' => $orderId]);
        $createdAt = $createdAtStatement->fetchColumn();

        if (!is_string($createdAt)) {
            throw new RuntimeException('The created order could not be reloaded.');
        }

        return new Order((string) $orderId, $total, 'pending', $createdAt);
    }

    private function insertLine(int $orderId, OrderLineDraft $line): int
    {
        $productIdStatement = $this->connection->prepare(
            'SELECT id FROM products WHERE public_id = :public_id'
        );
        $productIdStatement->execute(['public_id' => $line->product()->id()]);
        $productId = $productIdStatement->fetchColumn();

        if ($productId === false) {
            throw new RuntimeException('The order product disappeared during checkout.');
        }

        $lineStatement = $this->connection->prepare(
            'INSERT INTO order_items (
                order_id,
                product_id,
                product_public_id,
                product_name,
                quantity,
                unit_price
             ) VALUES (
                :order_id,
                :product_id,
                :product_public_id,
                :product_name,
                :quantity,
                :unit_price
             )'
        );
        $lineStatement->execute([
            'order_id' => $orderId,
            'product_id' => (int) $productId,
            'product_public_id' => $line->product()->id(),
            'product_name' => $line->product()->name(),
            'quantity' => $line->quantity(),
            'unit_price' => $line->price()->amount()->toDecimal(),
        ]);

        return (int) $this->connection->lastInsertId();
    }

    private function insertSelections(int $orderItemId, OrderLineDraft $line): void
    {
        $selectionStatement = $this->connection->prepare(
            'INSERT INTO order_item_attributes (
                order_item_id,
                attribute_id,
                attribute_name,
                item_id,
                item_display_value,
                item_value
             ) VALUES (
                :order_item_id,
                :attribute_id,
                :attribute_name,
                :item_id,
                :item_display_value,
                :item_value
             )'
        );

        foreach ($line->selectedAttributes() as $selection) {
            $selectionStatement->execute([
                'order_item_id' => $orderItemId,
                'attribute_id' => $selection->attribute()->id(),
                'attribute_name' => $selection->attribute()->name(),
                'item_id' => $selection->item()->id(),
                'item_display_value' => $selection->item()->displayValue(),
                'item_value' => $selection->item()->value(),
            ]);
        }
    }
}
