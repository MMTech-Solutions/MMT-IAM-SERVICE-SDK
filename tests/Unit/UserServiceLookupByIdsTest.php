<?php

declare(strict_types=1);

namespace Mmt\IamServiceSdk\Tests\Unit;

use Mmt\IamServiceSdk\Domains\Users\UserService;
use Mmt\IamServiceSdk\TransportDrivers\Contracts\ActionResultInterface;
use Mmt\IamServiceSdk\TransportDrivers\Contracts\TransportInterface;
use Mmt\IamServiceSdk\TransportDrivers\Contracts\TransportPacket;
use Mmt\IamServiceSdk\TransportDrivers\Drivers\Http\IamResponseResult;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class UserServiceLookupByIdsTest extends TestCase
{
    #[Test]
    public function posts_bulk_path_with_user_ids_body(): void
    {
        $captured = null;

        $transport = new class($captured) implements TransportInterface
        {
            public function __construct(private mixed &$captured) {}

            public function send(TransportPacket $packet): ActionResultInterface
            {
                $this->captured = $packet;

                return IamResponseResult::fromSuccessResponse(json_encode([
                    'success' => true,
                    'data' => [],
                ]) ?: '{}', 200);
            }
        };

        config(['laravel-iam-service-sdk.api_prefix' => '/api/iam/v1']);

        $service = new UserService($transport);
        $ids = [
            '11111111-1111-1111-1111-111111111111',
            '22222222-2222-2222-2222-222222222222',
        ];

        $result = $service->lookupByIds($ids);

        $this->assertTrue($result->isSuccess());
        $this->assertInstanceOf(TransportPacket::class, $captured);
        $this->assertSame('/api/iam/v1/rbac/admin/users/bulk', $captured->endpoint);
        $this->assertSame(['user_ids' => $ids], $captured->payload);
        $this->assertSame('post', $captured->metadata['method'] ?? null);
    }

    #[Test]
    public function gets_customer_directory_with_filters_and_sort(): void
    {
        $captured = null;

        $transport = new class($captured) implements TransportInterface
        {
            public function __construct(private mixed &$captured) {}

            public function send(TransportPacket $packet): ActionResultInterface
            {
                $this->captured = $packet;

                return IamResponseResult::fromSuccessResponse(json_encode([
                    'success' => true,
                    'data' => [],
                    'meta' => ['pagination' => ['last_page' => 1, 'current_page' => 1, 'total' => 0]],
                ]) ?: '{}', 200);
            }
        };

        config(['laravel-iam-service-sdk.api_prefix' => '/api/iam/v1']);

        $service = new UserService($transport);
        $result = $service->searchCustomerDirectory([
            'page' => 1,
            'per_page' => 100,
            'sort_by' => 'name',
            'sort_direction' => 'asc',
            'filter' => [
                'email' => 'john@example.com',
                'name' => 'John',
            ],
        ]);

        $this->assertTrue($result->isSuccess());
        $this->assertInstanceOf(TransportPacket::class, $captured);
        $this->assertSame('/api/iam/v1/rbac/admin/customer-directory/users', $captured->endpoint);
        $this->assertSame('get', $captured->metadata['method'] ?? null);
        $this->assertSame(1, $captured->payload['page'] ?? null);
        $this->assertSame('name', $captured->payload['sort_by'] ?? null);
        $this->assertSame(['email' => 'john@example.com', 'name' => 'John'], $captured->payload['filter'] ?? null);
    }
}
