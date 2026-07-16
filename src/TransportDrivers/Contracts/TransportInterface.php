<?php

declare(strict_types=1);

namespace Mmt\IamServiceSdk\TransportDrivers\Contracts;

interface TransportInterface
{
    public function send(TransportPacket $packet): ActionResultInterface;
}
