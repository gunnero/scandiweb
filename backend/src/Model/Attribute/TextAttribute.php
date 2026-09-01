<?php

declare(strict_types=1);

namespace App\Model\Attribute;

final class TextAttribute extends AbstractAttribute
{
    public function type(): string
    {
        return 'text';
    }
}
