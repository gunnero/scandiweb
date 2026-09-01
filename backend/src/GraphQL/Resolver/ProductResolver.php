<?php

declare(strict_types=1);

namespace App\GraphQL\Resolver;

use App\Model\Product\AbstractProduct;
use App\Repository\ProductRepositoryInterface;

final class ProductResolver
{
    public function __construct(private readonly ProductRepositoryInterface $products)
    {
    }

    /** @param array{categoryName: string} $arguments @return list<AbstractProduct> */
    public function byCategory(mixed $root, array $arguments): array
    {
        return $this->products->byCategory($arguments['categoryName']);
    }

    /** @param array{id: string} $arguments */
    public function one(mixed $root, array $arguments): ?AbstractProduct
    {
        return $this->products->find($arguments['id']);
    }
}
