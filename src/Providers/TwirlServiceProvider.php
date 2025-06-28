<?php

declare(strict_types=1);

namespace Moonshine\Twirl\Providers;

use Illuminate\Support\ServiceProvider;

final class TwirlServiceProvider extends ServiceProvider
{
    private string $packageDir = __DIR__.'/../..';

    /**
     * @var array<int, string>
     */
    protected array $commands = [
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands($this->commands);
        }

        $this->publishes([
            $this->packageDir . '/public/js' => base_path('public/vendor/moonshine-twirl/js'),
        ], 'moonshine-twirl-assets');

        $this->publishes([
            $this->packageDir . '/config/moonshine_twirl.php' =>
                config_path('moonshine_twirl.php'),
        ], 'moonshine-twirl-config');

        $this->loadViewsFrom($this->packageDir . '/resources/views', 'twirl');

        $this->mergeConfigFrom(
            $this->packageDir . '/config/moonshine_twirl.php',
            'moonshine_twirl'
        );
    }
}
