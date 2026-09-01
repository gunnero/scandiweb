<?php

declare(strict_types=1);

namespace App\GraphQL;

use App\GraphQL\Resolver\AttributeResolver;
use App\GraphQL\Resolver\CategoryResolver;
use App\GraphQL\Resolver\OrderResolver;
use App\GraphQL\Resolver\ProductResolver;
use App\GraphQL\Type\AttributeItemType;
use App\GraphQL\Type\AttributeType;
use App\GraphQL\Type\CategoryType;
use App\GraphQL\Type\MutationType;
use App\GraphQL\Type\OrderType;
use App\GraphQL\Type\PriceType;
use App\GraphQL\Type\ProductType;
use App\GraphQL\Type\QueryType;
use GraphQL\Type\Schema;

final class SchemaFactory
{
    public function __construct(
        private readonly CategoryResolver $categoryResolver,
        private readonly ProductResolver $productResolver,
        private readonly AttributeResolver $attributeResolver,
        private readonly OrderResolver $orderResolver
    ) {
    }

    public function create(): Schema
    {
        $categoryType = new CategoryType();
        $attributeType = new AttributeType(new AttributeItemType());
        $productType = new ProductType(
            $attributeType,
            new PriceType(),
            $this->attributeResolver
        );
        $orderType = new OrderType();

        return new Schema([
            'query' => new QueryType(
                $categoryType,
                $productType,
                $this->categoryResolver,
                $this->productResolver
            ),
            'mutation' => new MutationType($orderType, $this->orderResolver),
        ]);
    }
}
