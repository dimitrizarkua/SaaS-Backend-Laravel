<?php

return [
    'gateway'  => env('PAYMENT_GATEWAY', 'pinpayment'),
    'gateways' => [
        'pinpayment' => [
            'options' => [
                'currency'  => env('PINPAYMENT_CURRENCY', 'USD'),
                'testMode'  => env('PINPAYMENT_TEST_MODE', true),
            ],
        ],
    ],
];
