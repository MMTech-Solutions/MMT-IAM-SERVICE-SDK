<?php

declare(strict_types=1);

namespace Mmt\IamServiceSdk;

use Mmt\IamServiceSdk\Domains\Auth\AuthService;
use Mmt\IamServiceSdk\Domains\Auth\Contracts\AuthServiceInterface;
use Mmt\IamServiceSdk\Domains\Health\Contracts\HealthServiceInterface;
use Mmt\IamServiceSdk\Domains\Health\HealthService;
use Mmt\IamServiceSdk\Domains\IbSettings\Contracts\IbSettingsServiceInterface;
use Mmt\IamServiceSdk\Domains\IbSettings\IbSettingsService;
use Mmt\IamServiceSdk\Domains\ReferralNetwork\Contracts\ReferralNetworkServiceInterface;
use Mmt\IamServiceSdk\Domains\ReferralNetwork\ReferralNetworkService;
use Mmt\IamServiceSdk\Domains\Users\Contracts\UserServiceInterface;
use Mmt\IamServiceSdk\Domains\Users\UserService;
use Mmt\IamServiceSdk\TransportDrivers\Contracts\TransportInterface;

final class IamService
{
    public function __construct(
        private readonly TransportInterface $transport,
    ) {}

    public function referralNetwork(): ReferralNetworkServiceInterface
    {
        return new ReferralNetworkService($this->transport);
    }

    public function ibSettings(): IbSettingsServiceInterface
    {
        return new IbSettingsService($this->transport);
    }

    public function auth(): AuthServiceInterface
    {
        return new AuthService($this->transport);
    }

    public function users(): UserServiceInterface
    {
        return new UserService($this->transport);
    }

    public function health(): HealthServiceInterface
    {
        return new HealthService($this->transport);
    }
}
