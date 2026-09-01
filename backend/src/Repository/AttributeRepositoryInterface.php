<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Attribute\AbstractAttribute;

interface AttributeRepositoryInterface
{
    /** @return list<AbstractAttribute> */
    public function forProduct(string $productId): array;
}
