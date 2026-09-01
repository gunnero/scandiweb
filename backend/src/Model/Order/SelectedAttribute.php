<?php

declare(strict_types=1);

namespace App\Model\Order;

use App\Model\Attribute\AbstractAttribute;
use App\Model\Attribute\AttributeItem;

final class SelectedAttribute
{
    public function __construct(
        private readonly AbstractAttribute $attribute,
        private readonly AttributeItem $item
    ) {
    }

    public function attribute(): AbstractAttribute
    {
        return $this->attribute;
    }

    public function item(): AttributeItem
    {
        return $this->item;
    }
}
