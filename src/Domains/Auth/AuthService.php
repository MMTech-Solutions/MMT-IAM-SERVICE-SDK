<?php

declare(strict_types=1);

namespace Mmt\IamServiceSdk\Domains\Auth;

use Mmt\IamServiceSdk\Domains\AbstractIamDomainService;
use Mmt\IamServiceSdk\Domains\Auth\Contracts\AuthServiceInterface;
use Mmt\IamServiceSdk\TransportDrivers\Contracts\ActionResultInterface;

final class AuthService extends AbstractIamDomainService implements AuthServiceInterface
{
    public function confirmTwoFactor(string $code, array $extraHeaders = []): ActionResultInterface
    {
        return $this->post(
            $this->apiPath('/auth/user/2fa/confirm'),
            ['code' => $code],
            ['headers' => $extraHeaders],
        );
    }
}
