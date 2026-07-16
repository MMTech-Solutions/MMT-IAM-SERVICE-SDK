<?php

declare(strict_types=1);

namespace Mmt\IamServiceSdk\Contracts;

interface CommandInterface
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
