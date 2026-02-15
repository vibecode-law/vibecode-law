<?php

return [
    'enabled' => env('VIDEO_ENABLED', false),

    'mux' => [
        'token_id' => env('MUX_TOKEN_ID'),
        'token_secret' => env('MUX_TOKEN_SECRET'),
    ],
];
