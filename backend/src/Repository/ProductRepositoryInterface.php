<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Product\AbstractProduct;

interface ProductRepositoryInterface
{
    /** @return list<AbstractProduct> */
    public function byCategory(string $categoryName): array;

    public function find(string $id): ?AbstractProduct;

    public function findForUpdate(string $id): ?AbstractProduct;
}
