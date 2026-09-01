<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Money;
use App\Model\Order\Order;
use App\Model\Order\OrderLineDraft;

interface OrderRepositoryInterface
{
    /** @param list<OrderLineDraft> $lines */
    public function create(Money $total, string $currencyLabel, array $lines): Order;
}
