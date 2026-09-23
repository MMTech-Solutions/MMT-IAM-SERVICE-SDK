<?php

declare(strict_types=1);

namespace Mmt\IamServiceSdk\Domains\ReferralNetwork\Contracts;

use Mmt\IamServiceSdk\TransportDrivers\Contracts\ActionResultInterface;

interface ReferralNetworkServiceInterface
{
    public function getUpline(string $userId, ?int $levels = null): ActionResultInterface;

    public function getDownline(string $userId, ?int $levels = null): ActionResultInterface;

    public function getDirectReferrals(string $userId): ActionResultInterface;

    public function getReferralChildren(
        string $ibUserId,
        ?string $parentUserId = null,
        int $page = 1,
        int $perPage = 100,
    ): ActionResultInterface;
}
