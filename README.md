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

Publish the resources and configuration:
```bash
php artisan vendor:publish --provider="Moonshine\Twirl\Providers\TwirlServiceProvider"
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

> [!CAUTION]
> All examples are insecure and serve only for development

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

Write frontend logic for connect Centrifugo with Twirl. First, install the package:
```shell
npm install centrifuge
```

Example:
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

    let token = await getToken();

    const centrifuge = new Centrifuge("ws://localhost:8000/connection/websocket", {
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

async function getToken(): Promise<string> {
    // Your endpoint to get a token
    const response = await axios.post('/centrifugo/token')

    return response.data.token;
}
```

CentrifugoController example:
```php
use MoonShine\Laravel\Http\Controllers\MoonShineController;
use phpcent\Client;

class CentrifugoController extends MoonShineController
{
    public function index()
    {
        $client = new Client(
            url: 'http://centrifugo-url:8000/api',
            apikey: '...',
            secret: '...'
        );

        return response()->json([
            'token' => $client->generateConnectionToken($this->auth()->user()->id, channels: [
                'twirl-channel'
            ]),
        ]);
    }
}
```

Centrifugo config example:
```json
{
    "client": {
        "token": {
            "hmac_secret_key": "bbe7d157-a253-4094-9759-06a8236543f9"
        },
        "allowed_origins": ["*"]
    },
    "http_api": {
        "key": "d7627bb6-2292-4911-82e1-615c0ed3eebb"
    },
    "channel": {
        "without_namespace": {
            "allow_subscribe_for_client": true,
            "allow_publish_for_client": true
        },
        "namespaces": [
            {
                "name": "twirl-channel"
            }
        ]
    },
    "admin": {
        "enabled": true,
        "password": "12345",
        "secret": "12345"
    }
}
```