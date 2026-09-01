<?php

declare(strict_types=1);

namespace App\Model\Category;

use App\Repository\ProductRepositoryInterface;

final class AllCategory extends AbstractCategory
{
    public function productsFrom(ProductRepositoryInterface $products): array
    {
        return $products->all();
    }
}
