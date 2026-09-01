<?php

declare(strict_types=1);

namespace App\GraphQL\Type;

use App\GraphQL\Resolver\AttributeResolver;
use App\Model\Product\AbstractProduct;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

final class ProductType extends ObjectType
{
    public function __construct(
        AttributeType $attributeType,
        PriceType $priceType,
        AttributeResolver $attributeResolver
    ) {
        parent::__construct([
            'name' => 'Product',
            'fields' => [
                'id' => [
                    'type' => Type::nonNull(Type::id()),
                    'resolve' => static fn (AbstractProduct $product): string => $product->id(),
                ],
                'name' => [
                    'type' => Type::nonNull(Type::string()),
                    'resolve' => static fn (AbstractProduct $product): string => $product->name(),
                ],
                'inStock' => [
                    'type' => Type::nonNull(Type::boolean()),
                    'resolve' => static fn (AbstractProduct $product): bool => $product->inStock(),
                ],
                'description' => [
                    'type' => Type::nonNull(Type::string()),
                    'resolve' => static fn (AbstractProduct $product): string => $product->description(),
                ],
                'categoryName' => [
                    'type' => Type::nonNull(Type::string()),
                    'resolve' => static fn (AbstractProduct $product): string => $product->categoryName(),
                ],
                'brand' => [
                    'type' => Type::nonNull(Type::string()),
                    'resolve' => static fn (AbstractProduct $product): string => $product->brand(),
                ],
                'gallery' => [
                    'type' => Type::nonNull(Type::listOf(Type::nonNull(Type::string()))),
                    'resolve' => static fn (AbstractProduct $product): array => $product->gallery(),
                ],
                'prices' => [
                    'type' => Type::nonNull(Type::listOf(Type::nonNull($priceType))),
                    'resolve' => static fn (AbstractProduct $product): array => $product->prices(),
                ],
                'attributes' => [
                    'type' => Type::nonNull(Type::listOf(Type::nonNull($attributeType))),
                    'resolve' => [$attributeResolver, 'forProduct'],
                ],
            ],
        ]);
    }
}
