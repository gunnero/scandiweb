<?php

declare(strict_types=1);

namespace App\Model;

final class Price
{
    public function __construct(
        private readonly Money $amount,
        private readonly string $currencyLabel,
        private readonly string $currencySymbol
    ) {
    }

    public function amount(): Money
    {
        return $this->amount;
    }

    public function currencyLabel(): string
    {
        return $this->currencyLabel;
    }

    public function currencySymbol(): string
    {
        return $this->currencySymbol;
    }

    /** @return array{amount: float, currencyLabel: string, currencySymbol: string} */
    public function toArray(): array
    {
        return [
            'amount' => $this->amount->toFloat(),
            'currencyLabel' => $this->currencyLabel,
            'currencySymbol' => $this->currencySymbol,
        ];
    }
}
