<?php

declare(strict_types=1);

namespace Mmt\IamServiceSdk\Domains\IbSettings\ObjectResponses;

use Mmt\IamServiceSdk\WireHydration\Attributes\WireMapped;

#[WireMapped]
final class IbNetworkSettings
{
    public function __construct(
        public readonly int $max_ib_levels,
        public readonly ?string $updated_at = null,
        public readonly ?string $updated_by = null,
    ) {}
}
