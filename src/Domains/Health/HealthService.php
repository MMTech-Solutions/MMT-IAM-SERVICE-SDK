<?php

declare(strict_types=1);

namespace Mmt\IamServiceSdk\Domains\Health;

use Mmt\IamServiceSdk\Domains\AbstractIamDomainService;
use Mmt\IamServiceSdk\Domains\Health\Contracts\HealthServiceInterface;
use Mmt\IamServiceSdk\TransportDrivers\Contracts\ActionResultInterface;

final class HealthService extends AbstractIamDomainService implements HealthServiceInterface
{
    public function health(): ActionResultInterface
    {
        return $this->get($this->apiPath('/health'));
    }
}
