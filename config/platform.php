<?php

return [
    'cpanel_database_prefix' => 'elimutai_',
    'plans' => [
        'free' => [
            'label' => 'Free',
            'projects' => 5,
            'databases_per_project' => 5,
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
