<?php

declare(strict_types=1);

namespace Mmt\IamServiceSdk\TransportDrivers\Contracts;

class TransportPacket
{
    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly string $endpoint,
        public readonly array $payload = [],
        public readonly array $metadata = [],
    ) {}
}
