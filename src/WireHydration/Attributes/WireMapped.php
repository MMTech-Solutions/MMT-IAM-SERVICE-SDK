<?php

declare(strict_types=1);

namespace Mmt\IamServiceSdk\WireHydration\Attributes;

use Attribute;

/**
 * Maps a DTO or a public property to a wire (JSON) shape.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
final class WireMapped
{
    public function __construct(
        public readonly ?string $wireKey = null,
    ) {}
}
