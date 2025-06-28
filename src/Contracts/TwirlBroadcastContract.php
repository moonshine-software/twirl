<?php

declare(strict_types=1);

namespace MoonShine\Twirl\Contracts;

use MoonShine\Twirl\DTO\TwirlData;

interface TwirlBroadcastContract
{
    public function send(string $channel, TwirlData $twirlData): void;
}