<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Category\AbstractCategory;

interface CategoryRepositoryInterface
{
    /** @return list<AbstractCategory> */
    public function all(): array;

    public function findByName(string $name): ?AbstractCategory;
}
