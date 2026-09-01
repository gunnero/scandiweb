<?php

declare(strict_types=1);

namespace App\Model\Attribute;

final class AttributeItem
{
    public function __construct(
        private readonly string $id,
        private readonly string $displayValue,
        private readonly string $value
    ) {
    }

    public function id(): string
    {
        return $this->id;
    }

    public function displayValue(): string
    {
        return $this->displayValue;
    }

    public function value(): string
    {
        return $this->value;
    }
}
