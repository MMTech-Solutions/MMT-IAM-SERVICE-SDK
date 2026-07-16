<?php

declare(strict_types=1);

namespace Mmt\IamServiceSdk\Domains\ReferralNetwork\ObjectResponses;

use Mmt\IamServiceSdk\WireHydration\Attributes\WireMapped;

#[WireMapped]
final class IbReferralUserItem
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $name = null,
        public readonly ?string $lastname = null,
        public readonly ?string $email = null,
        public readonly ?string $username = null,
        public readonly ?bool $is_ib = null,
        public readonly ?string $created_at = null,
    ) {}
}
