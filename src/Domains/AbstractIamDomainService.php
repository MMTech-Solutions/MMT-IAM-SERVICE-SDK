<?php

declare(strict_types=1);

namespace Mmt\IamServiceSdk\Domains;

use Mmt\IamServiceSdk\TransportDrivers\Contracts\ActionResultInterface;
use Mmt\IamServiceSdk\TransportDrivers\Contracts\TransportInterface;
use Mmt\IamServiceSdk\TransportDrivers\Contracts\TransportPacket;

abstract class AbstractIamDomainService
{
    public function __construct(
        protected readonly TransportInterface $transport,
    ) {}

    protected function apiPath(string $suffix): string
    {
        $prefix = (string) config('laravel-iam-service-sdk.api_prefix', '/api/iam/v1');

        return $prefix.$suffix;
    }

    /**
     * @param  array<string, mixed>  $query
     * @param  array<string, mixed>  $metadata
     */
    protected function get(string $path, array $query = [], array $metadata = []): ActionResultInterface
    {
        return $this->send('get', $path, $query, $metadata);
    }

    /**
     * @param  array<string, mixed>  $body
     * @param  array<string, mixed>  $metadata
     */
    protected function post(string $path, array $body = [], array $metadata = []): ActionResultInterface
    {
        return $this->send('post', $path, $body, $metadata);
    }

    protected function encodePathSegment(string $value): string
    {
        return rawurlencode($value);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $metadata
     */
    private function send(string $method, string $path, array $payload, array $metadata): ActionResultInterface
    {
        return $this->transport->send(new TransportPacket(
            endpoint: $path,
            payload: $payload,
            metadata: array_merge(['method' => $method], $metadata),
        ));
    }
}
