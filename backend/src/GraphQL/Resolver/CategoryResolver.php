<?php

declare(strict_types=1);

namespace App\GraphQL\Resolver;

use App\Model\Category\AbstractCategory;
use App\Repository\CategoryRepositoryInterface;

final class CategoryResolver
{
    public function __construct(private readonly CategoryRepositoryInterface $categories)
    {
    }

    /** @return list<AbstractCategory> */
    public function all(): array
    {
        return $this->categories->all();
    }
}
