<?php

declare(strict_types=1);

namespace App\Model\Attribute;

use InvalidArgumentException;

final class TextAttribute extends AbstractAttribute
{
    public function type(): string
    {
        return 'text';
    }

    protected function assertItemInvariant(AttributeItem $item): void
    {
        if (trim($item->displayValue()) === '' || trim($item->value()) === '') {
            throw new InvalidArgumentException('Text attribute options require display and stored values.');
        }
    }
}
