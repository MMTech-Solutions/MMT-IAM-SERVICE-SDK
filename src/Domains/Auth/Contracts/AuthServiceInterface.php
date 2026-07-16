<?php

declare(strict_types=1);

namespace Mmt\IamServiceSdk\Domains\Auth\Contracts;

use Mmt\IamServiceSdk\TransportDrivers\Contracts\ActionResultInterface;

interface AuthServiceInterface
{
    /**
     * @param  array<string, string>  $extraHeaders
     */
    public function confirmTwoFactor(string $code, array $extraHeaders = []): ActionResultInterface;
}
