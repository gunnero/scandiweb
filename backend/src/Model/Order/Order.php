<?php

declare(strict_types=1);

namespace App\Model\Order;

use App\Model\Money;

final class Order
{
    public function __construct(
        private readonly string $id,
        private readonly Money $total,
        private readonly string $status,
        private readonly string $createdAt
    ) {
    }

    public function id(): string
    {
        return $this->id;
    }

    public function total(): Money
    {
        return $this->total;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function createdAt(): string
    {
        return $this->createdAt;
    }
}
