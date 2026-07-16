<?php

declare(strict_types=1);

namespace Mmt\IamServiceSdk\Domains\Users;

use Mmt\IamServiceSdk\Domains\AbstractIamDomainService;
use Mmt\IamServiceSdk\Domains\Users\Contracts\UserServiceInterface;
use Mmt\IamServiceSdk\TransportDrivers\Contracts\ActionResultInterface;

final class UserService extends AbstractIamDomainService implements UserServiceInterface
{
    public function getProfile(string $userId): ActionResultInterface
    {
        return $this->get($this->apiPath('/rbac/admin/users/'.$this->encodePathSegment($userId)));
    }
}
