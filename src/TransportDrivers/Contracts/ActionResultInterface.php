<?php

declare(strict_types=1);

namespace Mmt\IamServiceSdk\TransportDrivers\Contracts;

use Mmt\IamServiceSdk\WireHydration\Attributes\WireMapped;
use Mmt\IamServiceSdk\WireHydration\WireHydrator;

/**
 * @template T
 */
interface ActionResultInterface
{
    public function isSuccess(): bool;

    public function isFailure(): bool;

    public function getCode(): string;

    public function getMessage(): ?string;

    public function getStatus(): ?int;

    /**
     * @param  class-string<T>|null  $castToFqcn
     * @return T
     */
    public function getData(?string $castToFqcn = null): mixed;

    /**
     * @template H of object
     *
     * @param  class-string<H>|null  $castToFqcn
     * @return ($castToFqcn is null ? mixed : (H|list<H>))
     */
    public function getMappedData(?string $castToFqcn = null): mixed;

    public function getErrorDetails(): mixed;

    public function getRawResponse(): string;
}
