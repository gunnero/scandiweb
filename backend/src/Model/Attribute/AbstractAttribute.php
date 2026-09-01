<?php

declare(strict_types=1);

namespace App\Model\Attribute;

abstract class AbstractAttribute
{
    /** @param list<AttributeItem> $items */
    public function __construct(
        private readonly string $id,
        private readonly string $name,
        private readonly array $items
    ) {
    }

    abstract public function type(): string;

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    /** @return list<AttributeItem> */
    public function items(): array
    {
        return $this->items;
    }

    public function accepts(string $itemId): bool
    {
        foreach ($this->items as $item) {
            if ($item->id() === $itemId) {
                return true;
            }
        }

        return false;
    }

    public function item(string $itemId): ?AttributeItem
    {
        foreach ($this->items as $item) {
            if ($item->id() === $itemId) {
                return $item;
            }
        }

        return null;
    }
}
