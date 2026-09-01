<?php

declare(strict_types=1);

namespace App\Model\Order;

use App\Model\Price;
use App\Model\Product\AbstractProduct;

final class OrderLineDraft
{
    /** @param list<SelectedAttribute> $selectedAttributes */
    public function __construct(
        private readonly AbstractProduct $product,
        private readonly int $quantity,
        private readonly Price $price,
        private readonly array $selectedAttributes
    ) {
    }

    public function product(): AbstractProduct
    {
        return $this->product;
    }

    public function quantity(): int
    {
        return $this->quantity;
    }

    public function price(): Price
    {
        return $this->price;
    }

    /** @return list<SelectedAttribute> */
    public function selectedAttributes(): array
    {
        return $this->selectedAttributes;
    }
}
