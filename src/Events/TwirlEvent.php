<?php

declare(strict_types=1);

namespace MoonShine\Twirl\Events;

use MoonShine\Twirl\Contracts\TwirlBroadcastContract;
use MoonShine\Twirl\DTO\TwirlData;
use MoonShine\Twirl\Enums\HtmlReloadAction;
use MoonShine\Twirl\Enums\TwirlEventType;

readonly class TwirlEvent
{
    public function __construct(
        protected string $selector,
        protected string $html,
        protected HtmlReloadAction $action
    ) {
    }

    public static function dispatch(string $selector, string $html, HtmlReloadAction $action = HtmlReloadAction::INNER_HTML): void
    {
        $htmlReload = new self($selector, $html, $action);
        $htmlReload->reload();
    }

    public function reload(): void
    {
        app(TwirlBroadcastContract::class)->send(
            $this->channel(),
            new TwirlData(TwirlEventType::TWIRL, [
                'selector' => $this->selector,
                'html' => $this->html,
                'action' => $this->action->value,
            ])
        );
    }

    public function channel(): string
    {
        return str(config('moonshine_twirl.prefix'))
            ->append('-')
            ->append(config('moonshine_twirl.channel'))
            ->value();
    }
}