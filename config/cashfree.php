<?php

return [
    'appId' => env('CASHFREE_APP_ID'),
    'secretKey' => env('CASHFREE_SECRET_KEY'),
    'testURL' => 'https://sandbox.cashfree.com',
    'prodURL' => 'https://ces-api.cashfree.com',
    'environment' => env('CASHFREE_ENVIRONMENT', 'sandbox'),

    'PG' => [
        'testURL' => 'https://sandbox.cashfree.com/pg',
    ],
];
