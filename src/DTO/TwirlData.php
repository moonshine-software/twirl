<?php

declare(strict_types=1);

namespace MoonShine\Twirl\DTO;

use MoonShine\Twirl\Enums\TwirlEventType;

readonly class TwirlData
{
    public function __construct(
        private TwirlEventType $event,
        private array $data,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'event' => $this->event->value,
            'data' => $this->data
        ];
    }
}