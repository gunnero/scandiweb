<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Model\Money;
use PHPUnit\Framework\TestCase;

final class MoneyTest extends TestCase
{
    public function testMoneyUsesExactDecimalArithmetic(): void
    {
        $total = Money::fromDecimal('0.10')
            ->add(Money::fromDecimal('0.20'))
            ->add(Money::fromDecimal('144.69')->multiply(2));

        self::assertSame('289.68', $total->toDecimal());
        self::assertSame(289.68, $total->toFloat());
    }
}
