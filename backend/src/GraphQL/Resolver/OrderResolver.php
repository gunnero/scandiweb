<?php

declare(strict_types=1);

namespace App\GraphQL\Resolver;

use App\Application\PlaceOrderService;
use App\Model\Order\Order;

final class OrderResolver
{
    public function __construct(private readonly PlaceOrderService $placeOrder)
    {
    }

    /** @param array{items: list<array<string, mixed>>} $arguments */
    public function create(mixed $root, array $arguments): Order
    {
        return $this->placeOrder->place($arguments['items']);
    }
}
