<?php

return [
    'enabled' => env('VIDEO_ENABLED', false),

    'mux' => [
        'token_id' => env('MUX_TOKEN_ID'),
        'token_secret' => env('MUX_TOKEN_SECRET'),
        'signing_key_id' => env('MUX_SIGNING_KEY_ID'),
        'signing_private_key' => env('MUX_SIGNING_PRIVATE_KEY'),
    ],
];
