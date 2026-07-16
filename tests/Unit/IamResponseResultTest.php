<?php

declare(strict_types=1);

namespace Mmt\IamServiceSdk\Tests\Unit;

use Mmt\IamServiceSdk\TransportDrivers\Drivers\Http\IamResponseResult;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class IamResponseResultTest extends TestCase
{
    #[Test]
    public function decodes_success_envelope(): void
    {
        $result = IamResponseResult::fromSuccessResponse(json_encode([
            'success' => true,
            'data' => ['max_ib_levels' => 5],
        ]), 200);

        $this->assertTrue($result->isSuccess());
        $this->assertSame('OK', $result->getCode());
        $this->assertSame(200, $result->getStatus());
        $this->assertSame(['max_ib_levels' => 5], $result->getData());
    }

    #[Test]
    public function decodes_error_envelope_with_meta_message(): void
    {
        $result = IamResponseResult::fromErrorResponse(json_encode([
            'success' => false,
            'meta' => ['message' => 'Invalid code'],
            'error' => ['code' => 'INVALID_2FA'],
        ]), 422);

        $this->assertTrue($result->isFailure());
        $this->assertSame('INVALID_2FA', $result->getCode());
        $this->assertSame('Invalid code', $result->getMessage());
        $this->assertSame(422, $result->getStatus());
    }

    #[Test]
    public function maps_user_not_ib_code(): void
    {
        $result = IamResponseResult::fromErrorResponse(json_encode([
            'success' => false,
            'error' => ['code' => 'USER_NOT_IB'],
        ]), 422);

        $this->assertSame('USER_NOT_IB', $result->getCode());
        $this->assertSame(422, $result->getStatus());
    }
}
