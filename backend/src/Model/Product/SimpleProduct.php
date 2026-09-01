<?php

declare(strict_types=1);

namespace App\Model\Product;

use App\Exception\UserInputException;

final class SimpleProduct extends AbstractProduct
{
    protected function assertSelections(array $attributes, array $selectedAttributes): void
    {
        if ($attributes !== [] || $selectedAttributes !== []) {
            throw new UserInputException('This product does not accept attribute selections.');
        }
    }
}
