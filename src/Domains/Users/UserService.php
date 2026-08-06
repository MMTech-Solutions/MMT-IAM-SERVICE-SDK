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

    /**
     * @param  list<string>  $userIds
     */
    public function lookupByIds(array $userIds): ActionResultInterface
    {
        return $this->post($this->apiPath('/rbac/admin/users/bulk'), [
            'user_ids' => array_values($userIds),
        ]);
    }

    /**
     * @param  array{
     *     q?: string|null,
     *     page?: int,
     *     per_page?: int,
     *     sort_by?: string|null,
     *     sort_direction?: string|null,
     *     filter?: array<string, string|null>
     * }  $params
     */
    public function searchCustomerDirectory(array $params = []): ActionResultInterface
    {
        $query = array_filter(
            $params,
            static fn (mixed $value): bool => $value !== null && $value !== [],
        );

        return $this->get($this->apiPath('/rbac/admin/customer-directory/users'), $query);
    }
}
