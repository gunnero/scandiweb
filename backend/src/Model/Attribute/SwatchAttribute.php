<?php

declare(strict_types=1);

namespace App\Model\Attribute;

use InvalidArgumentException;

final class SwatchAttribute extends AbstractAttribute
{
    public function type(): string
    {
        return 'swatch';
    }

    protected function assertItemInvariant(AttributeItem $item): void
    {
        $hexColor = '/\A#[0-9a-f]{6}\z/i';

        if (preg_match($hexColor, $item->value()) !== 1) {
            throw new InvalidArgumentException('Swatch attribute options require a hexadecimal color value.');
        }
    }
}
