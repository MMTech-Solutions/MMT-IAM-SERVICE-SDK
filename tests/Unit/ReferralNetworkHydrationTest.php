<?php

declare(strict_types=1);

namespace Mmt\IamServiceSdk\Tests\Unit;

use Mmt\IamServiceSdk\Domains\ReferralNetwork\ObjectResponses\IbDownlineLevelItem;
use Mmt\IamServiceSdk\Domains\ReferralNetwork\ObjectResponses\IbDownlineResponse;
use Mmt\IamServiceSdk\Domains\ReferralNetwork\ObjectResponses\IbReferralUserItem;
use Mmt\IamServiceSdk\Domains\ReferralNetwork\ObjectResponses\IbUplineLevelItem;
use Mmt\IamServiceSdk\Domains\ReferralNetwork\ObjectResponses\IbUplineResponse;
use Mmt\IamServiceSdk\TransportDrivers\Drivers\Http\IamResponseResult;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ReferralNetworkHydrationTest extends TestCase
{
    #[Test]
    public function hydrates_upline_levels_wrapping_each_user(): void
    {
        $result = IamResponseResult::fromSuccessResponse(json_encode([
            'success' => true,
            'data' => [
                'levels' => 3,
                'upline' => [
                    [
                        'level' => 1,
                        'user' => [
                            'id' => 'sub-ib-id',
                            'name' => 'Sub',
                            'lastname' => 'Ib',
                            'email' => 'sub@example.com',
                            'username' => 'sub',
                            'is_ib' => true,
                            'created_at' => '2026-07-01T00:00:00+00:00',
                        ],
                    ],
                    [
                        'level' => 2,
                        'user' => ['id' => 'root-ib-id'],
                    ],
                ],
            ],
        ]), 200);

        /** @var IbUplineResponse $payload */
        $payload = $result->getMappedData(IbUplineResponse::class);

        $this->assertSame(3, $payload->levels);
        $this->assertCount(2, $payload->upline);

        $this->assertContainsOnlyInstancesOf(IbUplineLevelItem::class, $payload->upline);

        $this->assertSame(1, $payload->upline[0]->level);
        $this->assertInstanceOf(IbReferralUserItem::class, $payload->upline[0]->user);
        $this->assertSame('sub-ib-id', $payload->upline[0]->user->id);
        $this->assertSame('sub@example.com', $payload->upline[0]->user->email);
        $this->assertTrue($payload->upline[0]->user->is_ib);

        $this->assertSame(2, $payload->upline[1]->level);
        $this->assertSame('root-ib-id', $payload->upline[1]->user->id);
        $this->assertNull($payload->upline[1]->user->email);
    }

    #[Test]
    public function hydrates_downline_levels_ignoring_counts(): void
    {
        $result = IamResponseResult::fromSuccessResponse(json_encode([
            'success' => true,
            'data' => [
                'levels' => 2,
                'downline' => [
                    [
                        'level' => 1,
                        'users' => [
                            ['id' => 'client-a'],
                            ['id' => 'client-b'],
                        ],
                        'counts' => ['total' => 2, 'clients' => 2, 'sub_ibs' => 0],
                    ],
                ],
            ],
        ]), 200);

        /** @var IbDownlineResponse $payload */
        $payload = $result->getMappedData(IbDownlineResponse::class);

        $this->assertCount(1, $payload->downline);
        $this->assertContainsOnlyInstancesOf(IbDownlineLevelItem::class, $payload->downline);
        $this->assertSame(1, $payload->downline[0]->level);
        $this->assertSame(
            ['client-a', 'client-b'],
            array_map(fn (IbReferralUserItem $user): string => $user->id, $payload->downline[0]->users),
        );
    }
}
