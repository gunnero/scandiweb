<?php

declare(strict_types=1);

namespace App\GraphQL\Type;

use App\Model\Price;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

final class PriceType extends ObjectType
{
    public function __construct()
    {
        parent::__construct([
            'name' => 'Price',
            'fields' => [
                'amount' => [
                    'type' => Type::nonNull(Type::float()),
                    'resolve' => static fn (Price $price): float => $price->amount()->toFloat(),
                ],
                'currencyLabel' => [
                    'type' => Type::nonNull(Type::string()),
                    'resolve' => static fn (Price $price): string => $price->currencyLabel(),
                ],
                'currencySymbol' => [
                    'type' => Type::nonNull(Type::string()),
                    'resolve' => static fn (Price $price): string => $price->currencySymbol(),
                ],
            ],
        ]);
    }
}
