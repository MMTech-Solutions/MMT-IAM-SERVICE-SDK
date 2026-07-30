<?php

declare(strict_types=1);

namespace Mmt\IamServiceSdk\Domains\ReferralNetwork\ObjectResponses;

use Mmt\IamServiceSdk\WireHydration\Attributes\WireMapped;

#[WireMapped]
final class IbUplineLevelItem
{
    public function __construct(
        public readonly int $level,
        public readonly IbReferralUserItem $user,
    ) {}
}
