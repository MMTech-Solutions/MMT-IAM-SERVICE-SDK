<?php

declare(strict_types=1);

namespace Mmt\IamServiceSdk\Exceptions;

use RuntimeException;

final class IamApiException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly string $errorCode = 'IAM_API_ERROR',
        public readonly ?int $httpStatus = null,
    ) {
        parent::__construct($message);
    }
}
