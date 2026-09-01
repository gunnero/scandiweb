<?php

declare(strict_types=1);

namespace App\Model\Category;

use App\Model\Product\AbstractProduct;
use App\Repository\ProductRepositoryInterface;

abstract class AbstractCategory
{
    public function __construct(
        private readonly string $id,
        private readonly string $name
    ) {
    }

    /** @return list<AbstractProduct> */
    abstract public function productsFrom(ProductRepositoryInterface $products): array;

    final public function id(): string
    {
        return $this->id;
    }

    final public function name(): string
    {
        return $this->name;
    }
}
