<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel(config('twirl.prefix') . '-{channel}', function () {
    return true;
}, ['guards' => [config('moonshine.auth.guard')]]);