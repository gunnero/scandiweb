<?php

declare(strict_types=1);

namespace App\GraphQL\Resolver;

use App\Model\Attribute\AbstractAttribute;
use App\Model\Product\AbstractProduct;
use App\Repository\AttributeRepositoryInterface;

final class AttributeResolver
{
    public function __construct(private readonly AttributeRepositoryInterface $attributes)
    {
    }

    /** @return list<AbstractAttribute> */
    public function forProduct(AbstractProduct $product): array
    {
        return $this->attributes->forProduct($product->id());
    }
}
