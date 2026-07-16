<?php

declare(strict_types=1);

namespace Mmt\IamServiceSdk\TransportDrivers\Drivers\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Mmt\IamServiceSdk\TransportDrivers\Contracts\ActionResultInterface;
use Mmt\IamServiceSdk\TransportDrivers\Contracts\TransportInterface;
use Mmt\IamServiceSdk\TransportDrivers\Contracts\TransportPacket;

final class IamHttpClient implements TransportInterface
{
    private readonly Client $http;

    public function __construct()
    {
        $this->http = new Client([
            'headers' => $this->defaultHeaders(),
            'timeout' => (float) config('laravel-iam-service-sdk.timeout', 30),
        ]);
    }

    public function send(TransportPacket $packet): ActionResultInterface
    {
        $uri = $this->resolveRequestUri($packet->endpoint);

        if ($uri === null) {
            return IamResponseResult::fromFatalError(
                'IAM base URL is not configured. Set USER_IAM_BASE_URL or RBAC_IAM_BASE_URL.',
            );
        }

        $method = strtolower((string) ($packet->metadata['method'] ?? 'get'));
        $timeout = $packet->metadata['timeout'] ?? null;
        $extraHeaders = is_array($packet->metadata['headers'] ?? null) ? $packet->metadata['headers'] : [];

        $options = [
            'headers' => array_merge($this->defaultHeaders(), $extraHeaders),
        ];

        if ($timeout !== null) {
            $options['timeout'] = (float) $timeout;
        }

        if (in_array($method, ['get', 'delete'], true)) {
            if ($packet->payload !== []) {
                $options['query'] = $packet->payload;
            }
        } else {
            $options['json'] = $packet->payload;
        }

        try {
            $response = $this->http->request($method, $uri, $options);
            $raw = (string) $response->getBody();
            $status = $response->getStatusCode();

            if ($status >= 400) {
                return IamResponseResult::fromErrorResponse($raw, $status);
            }

            return IamResponseResult::fromSuccessResponse($raw, $status);
        } catch (ClientException $exception) {
            $response = $exception->getResponse();
            if ($response !== null) {
                return IamResponseResult::fromErrorResponse(
                    (string) $response->getBody(),
                    $response->getStatusCode(),
                );
            }

            return IamResponseResult::fromFatalError($exception->getMessage());
        } catch (ConnectException $exception) {
            return IamResponseResult::fromFatalError(
                'Connection to IAM API failed: '.$exception->getMessage(),
            );
        } catch (RequestException $exception) {
            $response = $exception->getResponse();
            if ($response !== null) {
                return IamResponseResult::fromErrorResponse(
                    (string) $response->getBody(),
                    $response->getStatusCode(),
                );
            }

            return IamResponseResult::fromFatalError($exception->getMessage());
        }
    }

    /**
     * @return array<string, string>
     */
    private function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-Internal-Token' => (string) config('laravel-iam-service-sdk.api_key', ''),
            'X-Internal-Source' => (string) config('laravel-iam-service-sdk.source_service', 'ms-broker'),
        ];
    }

    private function resolveRequestUri(string $endpoint): ?string
    {
        if (str_starts_with($endpoint, 'http://') || str_starts_with($endpoint, 'https://')) {
            return $endpoint;
        }

        $baseUrl = rtrim((string) config('laravel-iam-service-sdk.base_url'), '/');

        if ($baseUrl === '') {
            return null;
        }

        return $baseUrl.(str_starts_with($endpoint, '/') ? $endpoint : '/'.$endpoint);
    }
}
