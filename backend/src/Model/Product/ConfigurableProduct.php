<?php

declare(strict_types=1);

namespace App\Model\Product;

use App\Exception\UserInputException;

final class ConfigurableProduct extends AbstractProduct
{
    protected function assertSelections(array $attributes, array $selectedAttributes): void
    {
        if (count($attributes) !== count($selectedAttributes)) {
            throw new UserInputException('Select one option for every product attribute.');
        }

        foreach ($attributes as $attribute) {
            $itemId = $selectedAttributes[$attribute->id()] ?? null;

            if ($itemId === null || !$attribute->accepts($itemId)) {
                throw new UserInputException(
                    sprintf('Select a valid option for %s.', $attribute->name())
                );
            }
        }
    }
}
