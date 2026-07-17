<?php

return [
    'smsroot' => [
        'api_key' => env('SMSROOT_API_KEY', 'dummy'),
        'sender_id' => env('SMSROOT_SENDER_ID', 'OSTPLS'),
        'template_id' => env('SMSROOT_TEMPLATE_ID', 'dummy'),
        'route_id' => env('SMSROOT_ROUTE_ID', '13'),
        'campaign' => env('SMSROOT_CAMPAIGN', '0'),
    ],
];
