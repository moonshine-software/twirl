# Twirl - WebSocket component for MoonShine

## Install

```bash
composer require moonshine/twirl
```

## Quick start

Add Twirl component in your MoonShineLayot

```php
use MoonShine\Twirl\Components\Twirl;

Twirl::make(),
```

Now you can trigger the event and update your component

```php
use MoonShine\Twirl\Events\TwirlEvent;

TwirlEvent::dispatch(
    selector: '.your-selector' . $id,
    (string) Badge::make(),
    HtmlReloadAction::OUTER_HTML
);
```

## Settings for Centrifugo
Centrifugo backend example

```php
<?php

/**
 * @see https://github.com/centrifugal/phpcent
 */

declare(strict_types=1);

namespace App\Services;

use Throwable;
use phpcent\Client;
use MoonShine\Twirl\DTO\TwirlData;
use MoonShine\Twirl\Contracts\TwirlBroadcastContract;

final class Centrifugo implements TwirlBroadcastContract
{
    public function send(string $channel, TwirlData $twirlData): void
    {
        try {
            $client = new Client(config('app.centrifugo.host'). '/api', config('app.centrifugo.api-key'));
            $client->publish($channel, $twirlData->toArray());
        } catch (Throwable $e) {
            report($e);
        }
    }
}
```

Add into provider
```php
$this->app->bind(TwirlBroadcastContract::class, Centrifugo::class);
```

Centrifugo frontend ts example
```ts
import { Centrifuge, PublicationContext } from "centrifuge";
import axios from "axios";

declare global {
    interface Window {
        MoonShine: {
            onCallback: (name: string, callback: Function) => void;
        }
    }

    interface ImportMeta {
        env: {
            [key: string]: string;
        }
    }
}

document.addEventListener("moonshine:init", async () => {
    if (! window.MoonShine) {
        console.error('MoonShine is not initialized');
        return;
    }

    let token = await getOrCreateToken();

    const wsUrl = getWsURL()
    const centrifuge = new Centrifuge(wsUrl, {
        token: token
    });

    centrifuge.on('connected', () => {
        document.dispatchEvent(new CustomEvent('moonshine:twirl'));
    }).connect();

    window.MoonShine.onCallback('onTwirl', function(channel: string, onTwirl: (data: any) => void): void {
        if(centrifuge.getSubscription(channel) !== null) {
            return;
        }

        const sub = centrifuge.newSubscription(channel);

        sub.on('publication', function(ctx: PublicationContext): void {
            onTwirl(ctx.data);
        }).on('error', (error): void => {
            console.log(error)
        })
            .subscribe()
    });
});
```