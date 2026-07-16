<?php

declare(strict_types=1);

namespace Mmt\IamServiceSdk\Domains\IbSettings;

use Mmt\IamServiceSdk\Domains\AbstractIamDomainService;
use Mmt\IamServiceSdk\Domains\IbSettings\Contracts\IbSettingsServiceInterface;
use Mmt\IamServiceSdk\TransportDrivers\Contracts\ActionResultInterface;

final class IbSettingsService extends AbstractIamDomainService implements IbSettingsServiceInterface
{
    public function getNetworkSettings(): ActionResultInterface
    {
        return $this->get($this->apiPath('/rbac/admin/ib-network/settings'));
    }
}
