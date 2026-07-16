<?php

declare(strict_types=1);

namespace Mmt\IamServiceSdk\Domains\IbSettings\Contracts;

use Mmt\IamServiceSdk\TransportDrivers\Contracts\ActionResultInterface;

interface IbSettingsServiceInterface
{
    public function getNetworkSettings(): ActionResultInterface;
}
