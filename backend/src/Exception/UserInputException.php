<?php

declare(strict_types=1);

namespace App\Exception;

use GraphQL\Error\ClientAware;
use RuntimeException;

final class UserInputException extends RuntimeException implements ClientAware
{
    public function isClientSafe(): bool
    {
        return true;
    }

    public function getCategory(): string
    {
        return 'validation';
    }
}
