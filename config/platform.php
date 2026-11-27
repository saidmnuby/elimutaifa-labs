<?php

return [
    'cpanel_database_prefix' => 'elimutai_',
    'plus_pricing' => [
        'monthly' => (int) env('PLUS_MONTHLY_PRICE', 3000),
        'yearly' => (int) env('PLUS_YEARLY_PRICE', 20000),
    ],
    'payment_accounts' => [
        'crdb' => ['label' => 'CRDB', 'number' => '0152933258100', 'type' => 'Bank account'],
        'nmb' => ['label' => 'NMB', 'number' => '21310045326', 'type' => 'Bank account'],
        'mixx' => ['label' => 'Mixx by Yas', 'number' => '0715874294', 'type' => 'Mobile money'],
        'airtel' => ['label' => 'Airtel Money', 'number' => '0780834952', 'type' => 'Mobile money'],
        'halotel' => ['label' => 'HaloPesa', 'number' => '0629941738', 'type' => 'Mobile money'],
        'mpesa' => ['label' => 'M-Pesa', 'number' => '0760 075629', 'type' => 'Mobile money'],
    ],
    'payment_account_name' => 'SAID MOHAMED ABDALLAH',
    'payment_support_phone' => '0715874294',
    'payment_support_emails' => ['info@elimutaifa.co.tz', 'elimutaifa@gmail.com'],
    'plans' => [
        'free' => [
            'label' => 'Free',
            'projects' => 3,
            'databases_per_project' => 3,
            'storage_mb' => 50,
        ],
        'plus' => [
            'label' => 'Plus',
            'projects' => null,
            'databases_per_project' => null,
            'storage_mb' => null,
        ],
    ],
];
