<?php

declare(strict_types=1);

namespace App\Model;

use InvalidArgumentException;

final class Money
{
    private function __construct(private readonly int $minorAmount)
    {
    }

    public static function zero(): self
    {
        return new self(0);
    }

    public static function fromDecimal(string $amount): self
    {
        if (preg_match('/^-?\d+(?:\.\d{1,2})?$/', $amount) !== 1) {
            throw new InvalidArgumentException('Money values must have at most two decimal places.');
        }

        $negative = str_starts_with($amount, '-');
        $unsigned = ltrim($amount, '-');
        [$whole, $fraction] = array_pad(explode('.', $unsigned, 2), 2, '');
        $minorAmount = ((int) $whole * 100) + (int) str_pad($fraction, 2, '0');

        return new self($negative ? -$minorAmount : $minorAmount);
    }

    public function add(self $other): self
    {
        return new self($this->minorAmount + $other->minorAmount);
    }

    public function multiply(int $quantity): self
    {
        return new self($this->minorAmount * $quantity);
    }

    public function toDecimal(): string
    {
        $absolute = abs($this->minorAmount);
        $prefix = $this->minorAmount < 0 ? '-' : '';

        return sprintf('%s%d.%02d', $prefix, intdiv($absolute, 100), $absolute % 100);
    }

    public function toFloat(): float
    {
        return (float) $this->toDecimal();
    }
}
