<?php

declare(strict_types=1);

namespace App\GraphQL\Type;

use App\Model\Order\Order;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

final class OrderType extends ObjectType
{
    public function __construct()
    {
        parent::__construct([
            'name' => 'Order',
            'fields' => [
                'id' => [
                    'type' => Type::nonNull(Type::id()),
                    'resolve' => static fn (Order $order): string => $order->id(),
                ],
                'total' => [
                    'type' => Type::nonNull(Type::float()),
                    'resolve' => static fn (Order $order): float => $order->total()->toFloat(),
                ],
                'status' => [
                    'type' => Type::nonNull(Type::string()),
                    'resolve' => static fn (Order $order): string => $order->status(),
                ],
                'createdAt' => [
                    'type' => Type::nonNull(Type::string()),
                    'resolve' => static fn (Order $order): string => $order->createdAt(),
                ],
            ],
        ]);
    }
}
