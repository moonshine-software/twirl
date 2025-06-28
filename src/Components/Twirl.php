<?php

declare(strict_types=1);

namespace MoonShine\Twirl\Components;

use MoonShine\UI\Components\MoonShineComponent;

class Twirl extends MoonShineComponent
{
    protected string $view = 'twirl::components.twirl';

    protected function viewData(): array
    {
        return array_merge(parent::viewData(), [
            'channel' => str(config('moonshine_twirl.prefix'))
                ->append('-')
                ->append(config('moonshine_twirl.channel')),
        ]);
    }
}