<?php

declare(strict_types=1);

namespace App\GraphQL\Type;

use App\GraphQL\Resolver\CategoryResolver;
use App\GraphQL\Resolver\ProductResolver;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

final class QueryType extends ObjectType
{
    public function __construct(
        CategoryType $categoryType,
        ProductType $productType,
        CategoryResolver $categoryResolver,
        ProductResolver $productResolver
    ) {
        parent::__construct([
            'name' => 'Query',
            'fields' => [
                'categories' => [
                    'type' => Type::nonNull(Type::listOf(Type::nonNull($categoryType))),
                    'resolve' => [$categoryResolver, 'all'],
                ],
                'productsByCategory' => [
                    'type' => Type::nonNull(Type::listOf(Type::nonNull($productType))),
                    'args' => [
                        'categoryName' => Type::nonNull(Type::string()),
                    ],
                    'resolve' => [$productResolver, 'byCategory'],
                ],
                'product' => [
                    'type' => $productType,
                    'args' => [
                        'id' => Type::nonNull(Type::id()),
                    ],
                    'resolve' => [$productResolver, 'one'],
                ],
            ],
        ]);
    }
}
