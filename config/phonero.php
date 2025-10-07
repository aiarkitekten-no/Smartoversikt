<?php

return [
    'api_url' => env('PHONERO_API_URL', 'https://bedriftsnett-api.phonero.net'),
    'username' => env('PHONERO_USERNAME'),
    'password' => env('PHONERO_PASSWORD'),
    'customer_id' => env('PHONERO_CUSTOMER_ID', 1),
    'default_agent' => env('PHONERO_DEFAULT_AGENT', '41347577'),
    'default_cli' => env('PHONERO_DEFAULT_CLI', '69020070'),
];
