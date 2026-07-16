<?php

declare(strict_types=1);

namespace Mmt\IamServiceSdk\Domains\Users\ObjectResponses;

use Mmt\IamServiceSdk\WireHydration\Attributes\WireMapped;

#[WireMapped]
final class UserProfileItem
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $name = null,
        public readonly ?string $lastname = null,
        public readonly ?string $username = null,
        public readonly ?string $full_name = null,
        public readonly ?string $email = null,
        public readonly ?string $referer = null,
        public readonly ?int $iblink_id = null,
        public readonly ?float $ibratio = null,
        public readonly ?int $country_id = null,
        public readonly ?string $state = null,
        public readonly ?string $city = null,
        public readonly ?string $street = null,
        public readonly ?string $zipcode = null,
    ) {}
}
