<?php

return [
    'prism_server' => [
        // The middleware that will be applied to the Prism Server routes.
        'middleware' => [],
        'enabled' => env('PRISM_SERVER_ENABLED', false),
    ],
    'request_timeout' => env('PRISM_REQUEST_TIMEOUT', 30), // The timeout for requests in seconds.
    'providers' => [
        'anthropic' => [
            'api_key' => env('ANTHROPIC_API_KEY', ''),
            'version' => env('ANTHROPIC_API_VERSION', '2023-06-01'),
            'url' => env('ANTHROPIC_URL', 'https://api.anthropic.com/v1'),
            // Include beta strings as a comma separated list.
            'anthropic_beta' => env('ANTHROPIC_BETA', 'structured-outputs-2025-11-13'),
        ],
    ],
];
