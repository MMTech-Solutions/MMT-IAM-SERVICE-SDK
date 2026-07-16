<?php

declare(strict_types=1);

namespace Mmt\IamServiceSdk\Domains\Health\Contracts;

use Mmt\IamServiceSdk\TransportDrivers\Contracts\ActionResultInterface;

interface HealthServiceInterface
{
    public function health(): ActionResultInterface;
}
