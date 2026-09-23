<?php

declare(strict_types=1);

namespace Mmt\IamServiceSdk\Domains\ReferralNetwork;

use Mmt\IamServiceSdk\Domains\AbstractIamDomainService;
use Mmt\IamServiceSdk\Domains\ReferralNetwork\Contracts\ReferralNetworkServiceInterface;
use Mmt\IamServiceSdk\TransportDrivers\Contracts\ActionResultInterface;

final class ReferralNetworkService extends AbstractIamDomainService implements ReferralNetworkServiceInterface
{
    public function getUpline(string $userId, ?int $levels = null): ActionResultInterface
    {
        return $this->get(
            $this->userIbPath($userId, 'upline'),
            $this->levelsQuery($levels),
        );
    }

    public function getDownline(string $userId, ?int $levels = null): ActionResultInterface
    {
        return $this->get(
            $this->userIbPath($userId, 'referrals'),
            $this->levelsQuery($levels),
        );
    }

    public function getDirectReferrals(string $userId): ActionResultInterface
    {
        return $this->get($this->userIbPath($userId, 'referrals/direct'));
    }

    public function getReferralChildren(
        string $ibUserId,
        ?string $parentUserId = null,
        int $page = 1,
        int $perPage = 100,
    ): ActionResultInterface {
        $query = ['page' => max(1, $page), 'per_page' => min(100, max(1, $perPage))];

        if ($parentUserId !== null && trim($parentUserId) !== '') {
            $query['parent_user_id'] = $parentUserId;
        }

        return $this->get($this->userIbPath($ibUserId, 'referrals/children'), $query);
    }

    /**
     * @return array<string, int>
     */
    private function levelsQuery(?int $levels): array
    {
        if ($levels === null) {
            return [];
        }

        return ['levels' => $levels];
    }

    private function userIbPath(string $userId, string $suffix): string
    {
        return $this->apiPath('/rbac/admin/users/'.$this->encodePathSegment($userId).'/ib/'.$suffix);
    }
}
