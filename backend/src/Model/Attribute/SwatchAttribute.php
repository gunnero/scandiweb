<?php

declare(strict_types=1);

namespace App\Model\Attribute;

final class SwatchAttribute extends AbstractAttribute
{
    public function type(): string
    {
        return 'swatch';
    }
}
