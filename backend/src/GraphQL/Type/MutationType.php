<?php

declare(strict_types=1);

namespace App\GraphQL\Type;

use App\GraphQL\Resolver\OrderResolver;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

final class MutationType extends ObjectType
{
    public function __construct(OrderType $orderType, OrderResolver $orderResolver)
    {
        $selectedAttributeInput = new InputObjectType([
            'name' => 'SelectedAttributeInput',
            'fields' => [
                'attributeId' => Type::nonNull(Type::id()),
                'itemId' => Type::nonNull(Type::id()),
            ],
        ]);
        $orderItemInput = new InputObjectType([
            'name' => 'OrderItemInput',
            'fields' => [
                'productId' => Type::nonNull(Type::id()),
                'quantity' => Type::nonNull(Type::int()),
                'selectedAttributes' => Type::nonNull(
                    Type::listOf(Type::nonNull($selectedAttributeInput))
                ),
            ],
        ]);

        parent::__construct([
            'name' => 'Mutation',
            'fields' => [
                'createOrder' => [
                    'type' => Type::nonNull($orderType),
                    'args' => [
                        'items' => Type::nonNull(Type::listOf(Type::nonNull($orderItemInput))),
                    ],
                    'resolve' => [$orderResolver, 'create'],
                ],
            ],
        ]);
    }
}
