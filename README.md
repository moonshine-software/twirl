# Twirl - WebSocket component for MoonShine

**Twirl** is a lightweight, free component for integrating WebSocket updates into the MoonShine admin panel. It allows you to quickly implement real-time dynamic updates of interface elements using Centrifugo or other WebSocket servers.

**Twirl features:**

- Simple HTML component updates on events
- Easy integration with MoonShine and Centrifugo
- Minimal dependencies, maximum speed to launch

Twirl is ideal for basic scenarios of dynamic interface updates.
For advanced features—notifications, collaborative form editing, fragment updates, and integration with various WebSocket providers—use the full [Rush package](https://moonshine-laravel.com/plugins/rush).

## Requirements

- MoonShine 3.0+

## Install

```bash
composer require moonshine/twirl
```

## Quick start

Add Twirl component in your MoonShineLayout or page:

```php
use MoonShine\Twirl\Components\Twirl;

Twirl::make(),
```

Now you can trigger the event and update your component:

```php
use MoonShine\Twirl\Events\TwirlEvent;

TwirlEvent::dispatch(
    selector: '.your-selector' . $id,
    (string) Badge::make(),
    HtmlReloadAction::OUTER_HTML
);
```

**Twirl** is a thin wrapper around updating HTML elements and a convenient interface to plug into any WebSocket transport. **It does not run or configure WebSocket connections for you.**

You need make the bridge between Twirl and your WebSocket stack  by yourself:
- Backend: implement and bind your own broadcaster via `TwirlBroadcastContract` for any provider (Centrifugo, Pusher, Socket.IO, custom, etc.).
- Frontend: subscribe to your channels with your client and pass incoming payloads to `onTwirl` so Twirl can apply HTML updates.

Quick checklist:
- Implement `TwirlBroadcastContract` for your transport.
- Bind it in the container.
- On the frontend, set up subscriptions and forward publications to `onTwirl()` callback.

### Example for Centrifugo

Install library for work with Centrifugo:
```bash
composer require centrifugal/phpcent:~6.0
```

Up the Centrifugo instance and make some configs in your app (host, api-key, jwt-secret...).

Then implement `TwirlBroadcastContract` with connection to Centrifugo:

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

Add into provider:
```php
$this->app->bind(TwirlBroadcastContract::class, Centrifugo::class);
```

Write frontend logic for connect Centrifugo with Twirl:
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

    // need to implement getOrCreateToken() and getWsURL() by self.
    // You can see examples in your websocket-library documentation
    let token = await getOrCreateToken();
    const wsUrl = getWsURL();

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
