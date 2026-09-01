<?php

declare(strict_types=1);

namespace App\Application;

use App\Exception\UserInputException;
use App\Model\Attribute\AbstractAttribute;
use App\Model\Money;
use App\Model\Order\Order;
use App\Model\Order\OrderLineDraft;
use App\Model\Order\SelectedAttribute;
use App\Repository\AttributeRepositoryInterface;
use App\Repository\OrderRepositoryInterface;
use App\Repository\ProductRepositoryInterface;
use App\Repository\TransactionManagerInterface;

final class PlaceOrderService
{
    private const CURRENCY = 'USD';
    private const MAX_QUANTITY = 100;

    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly AttributeRepositoryInterface $attributes,
        private readonly OrderRepositoryInterface $orders,
        private readonly TransactionManagerInterface $transactions
    ) {
    }

    /** @param list<array<string, mixed>> $items */
    public function place(array $items): Order
    {
        if ($items === []) {
            throw new UserInputException('Add at least one product before placing an order.');
        }

        return $this->transactions->run(function () use ($items): Order {
            $lines = [];
            $total = Money::zero();

            foreach ($items as $item) {
                $quantity = $this->quantity($item['quantity'] ?? null);
                $productId = $this->productId($item['productId'] ?? null);
                $product = $this->products->findForUpdate($productId);

                if ($product === null) {
                    throw new UserInputException(sprintf('Product "%s" was not found.', $productId));
                }

                $attributes = $this->attributes->forProduct($productId);
                [$selectionMap, $selections] = $this->selections(
                    $attributes,
                    $item['selectedAttributes'] ?? []
                );
                $product->assertPurchasable($attributes, $selectionMap);
                $price = $product->price(self::CURRENCY);

                if ($price === null) {
                    throw new UserInputException(
                        sprintf('%s has no %s price.', $product->name(), self::CURRENCY)
                    );
                }

                $lines[] = new OrderLineDraft($product, $quantity, $price, $selections);
                $total = $total->add($price->amount()->multiply($quantity));
            }

            return $this->orders->create($total, self::CURRENCY, $lines);
        });
    }

    private function quantity(mixed $quantity): int
    {
        if (!is_int($quantity) || $quantity < 1 || $quantity > self::MAX_QUANTITY) {
            throw new UserInputException('Quantity must be between 1 and 100.');
        }

        return $quantity;
    }

    private function productId(mixed $productId): string
    {
        if (!is_string($productId) || trim($productId) === '') {
            throw new UserInputException('Every order item must identify a product.');
        }

        return $productId;
    }

    /**
     * @param list<AbstractAttribute> $attributes
     * @param mixed $inputSelections
     * @return array{array<string, string>, list<SelectedAttribute>}
     */
    private function selections(array $attributes, mixed $inputSelections): array
    {
        if (!is_array($inputSelections)) {
            throw new UserInputException('Selected attributes must be a list.');
        }

        $attributesById = [];
        foreach ($attributes as $attribute) {
            $attributesById[$attribute->id()] = $attribute;
        }

        $selectionMap = [];
        $selections = [];

        foreach ($inputSelections as $inputSelection) {
            if (!is_array($inputSelection)) {
                throw new UserInputException('Every selected attribute must be an object.');
            }

            $attributeId = $inputSelection['attributeId'] ?? null;
            $itemId = $inputSelection['itemId'] ?? null;

            if (!is_string($attributeId) || !is_string($itemId)) {
                throw new UserInputException('Selected attribute identifiers must be strings.');
            }

            if (array_key_exists($attributeId, $selectionMap)) {
                throw new UserInputException(sprintf('Attribute "%s" was selected twice.', $attributeId));
            }

            $attribute = $attributesById[$attributeId] ?? null;
            $attributeItem = $attribute?->item($itemId);

            if ($attribute === null || $attributeItem === null) {
                throw new UserInputException('An invalid product option was selected.');
            }

            $selectionMap[$attributeId] = $itemId;
            $selections[] = new SelectedAttribute($attribute, $attributeItem);
        }

        return [$selectionMap, $selections];
    }
}
