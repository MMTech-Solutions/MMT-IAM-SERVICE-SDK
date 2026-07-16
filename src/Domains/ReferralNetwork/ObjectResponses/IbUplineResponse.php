<?php

declare(strict_types=1);

namespace Mmt\IamServiceSdk\Domains\ReferralNetwork\ObjectResponses;

use Mmt\IamServiceSdk\WireHydration\Attributes\WireMapped;

#[WireMapped]
final class IbUplineResponse
{
    public function __construct(
        public readonly ?int $levels = null,
        /** @var IbReferralUserItem[] */
        public readonly array $upline = [],
    ) {}
}
