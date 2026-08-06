<?php

declare(strict_types=1);

namespace Mmt\IamServiceSdk\Domains\Users\Contracts;

use Mmt\IamServiceSdk\TransportDrivers\Contracts\ActionResultInterface;

interface UserServiceInterface
{
    public function getProfile(string $userId): ActionResultInterface;

    /**
     * Bulk user lookup by UUID list (IAM POST /rbac/admin/users/bulk).
     *
     * @param  list<string>  $userIds
     */
    public function lookupByIds(array $userIds): ActionResultInterface;

    /**
     * Customer directory search/filters (IAM GET /rbac/admin/customer-directory/users).
     *
     * @param  array{
     *     q?: string|null,
     *     page?: int,
     *     per_page?: int,
     *     sort_by?: string|null,
     *     sort_direction?: string|null,
     *     filter?: array<string, string|null>
     * }  $params
     */
    public function searchCustomerDirectory(array $params = []): ActionResultInterface;
}
