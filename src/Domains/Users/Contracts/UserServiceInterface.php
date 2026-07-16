<?php

declare(strict_types=1);

namespace Mmt\IamServiceSdk\Domains\Users\Contracts;

use Mmt\IamServiceSdk\TransportDrivers\Contracts\ActionResultInterface;

interface UserServiceInterface
{
    public function getProfile(string $userId): ActionResultInterface;
}
