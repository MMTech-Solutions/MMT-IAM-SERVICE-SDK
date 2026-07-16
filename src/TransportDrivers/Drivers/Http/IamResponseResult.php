<?php

declare(strict_types=1);

namespace Mmt\IamServiceSdk\TransportDrivers\Drivers\Http;

use Mmt\IamServiceSdk\TransportDrivers\Contracts\ActionResultInterface;
use Mmt\IamServiceSdk\WireHydration\WireHydrator;

final class IamResponseResult implements ActionResultInterface
{
    public function __construct(
        private readonly string $code,
        private readonly bool $success,
        private readonly mixed $data = null,
        private readonly ?string $message = null,
        private readonly mixed $errorDetails = null,
        private readonly string $rawResponse = '',
        private readonly ?int $httpStatus = null,
    ) {}

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function isFailure(): bool
    {
        return ! $this->success;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function getStatus(): ?int
    {
        return $this->httpStatus;
    }

    public function getData(?string $castToFqcn = null): mixed
    {
        if ($castToFqcn && isset($this->data)) {
            if (is_array($this->data) && array_is_list($this->data)) {
                return array_map(fn ($item) => new $castToFqcn(...$item), $this->data);
            }

            if (is_array($this->data)) {
                return new $castToFqcn(...$this->data);
            }
        }

        return $this->data;
    }

    public function getMappedData(?string $castToFqcn = null): mixed
    {
        if ($castToFqcn === null) {
            return $this->data;
        }

        if ($this->data === null) {
            return null;
        }

        return (new WireHydrator())->hydrate($this->data, $castToFqcn);
    }

    public function getErrorDetails(): mixed
    {
        return $this->errorDetails;
    }

    public function getRawResponse(): string
    {
        return $this->rawResponse;
    }

    public static function fromSuccessResponse(string $rawResponse, int $httpStatus = 200): self
    {
        $decoded = json_decode($rawResponse, true);

        if (! is_array($decoded)) {
            return self::fromFatalError('Invalid JSON response from IAM.', $httpStatus);
        }

        $success = $decoded['success'] ?? null;

        if ($success !== true) {
            return self::fromErrorResponse($rawResponse, $httpStatus);
        }

        $data = $decoded['data'] ?? null;

        return new self(
            code: 'OK',
            success: true,
            data: $data,
            message: self::resolveSuccessMessage($decoded),
            rawResponse: $rawResponse,
            httpStatus: $httpStatus,
        );
    }

    public static function fromErrorResponse(string $rawResponse, int $httpStatus = 422): self
    {
        $decoded = json_decode($rawResponse, true);

        if (! is_array($decoded)) {
            return new self(
                code: 'IAM_API_ERROR',
                success: false,
                message: 'IAM API request failed (HTTP '.$httpStatus.').',
                rawResponse: $rawResponse,
                httpStatus: $httpStatus,
            );
        }

        return new self(
            code: self::resolveErrorCode($decoded),
            success: false,
            message: self::resolveErrorMessage($decoded, $httpStatus),
            errorDetails: $decoded['error'] ?? $decoded,
            rawResponse: $rawResponse,
            httpStatus: $httpStatus,
        );
    }

    public static function fromFatalError(string $message, ?int $httpStatus = null): self
    {
        return new self(
            code: 'FATAL_ERROR',
            success: false,
            message: $message,
            httpStatus: $httpStatus,
        );
    }

    /**
     * @param  array<string, mixed>  $decoded
     */
    private static function resolveSuccessMessage(array $decoded): ?string
    {
        $meta = $decoded['meta'] ?? [];

        if (is_array($meta) && isset($meta['message']) && is_string($meta['message'])) {
            return $meta['message'];
        }

        if (isset($decoded['message']) && is_string($decoded['message'])) {
            return $decoded['message'];
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $decoded
     */
    private static function resolveErrorMessage(array $decoded, int $httpStatus): string
    {
        $meta = $decoded['meta'] ?? [];

        if (is_array($meta) && isset($meta['message']) && is_string($meta['message'])) {
            return $meta['message'];
        }

        if (isset($decoded['message']) && is_string($decoded['message'])) {
            return $decoded['message'];
        }

        $errorCode = self::resolveErrorCode($decoded);

        if ($errorCode !== 'IAM_API_ERROR') {
            return $errorCode;
        }

        return 'IAM API request failed (HTTP '.$httpStatus.').';
    }

    /**
     * @param  array<string, mixed>  $decoded
     */
    private static function resolveErrorCode(array $decoded): string
    {
        $error = $decoded['error'] ?? null;

        if (is_array($error) && isset($error['code']) && is_string($error['code']) && $error['code'] !== '') {
            return strtoupper($error['code']);
        }

        if (isset($decoded['code']) && is_string($decoded['code']) && $decoded['code'] !== '') {
            return strtoupper($decoded['code']);
        }

        $meta = $decoded['meta'] ?? [];

        if (is_array($meta) && isset($meta['code']) && is_string($meta['code']) && $meta['code'] !== '') {
            return strtoupper($meta['code']);
        }

        return 'IAM_API_ERROR';
    }
}
