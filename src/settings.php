<?php
return [
    'settings' => [
        'refresh_key' => $_ENV['REFRESH_KEY'] ?: FALSE,
        'displayErrorDetails' => (bool) $_ENV['DISPLAY_ERRORS'] ?: FALSE,
        'addContentLengthHeader' => false,
        'debug' => (bool) $_ENV['DEBUG'] ?: FALSE,
        'twig' => [
            'template_path' => __DIR__ . '/../templates/',
            'template_cache' => __DIR__ . '/../tmp/twig/',
            'twig_debug' => (bool) $_ENV['DEBUG'] ?: FALSE,
            'date_format' => 'Y-m-d H:i:s',
            'auto_reload' => FALSE
        ],
        'database' => [
            'primary' => [
                'host' => $_ENV['DB_HOST'] ?: '',
                'port' => $_ENV['DB_PORT'] ?: '',
                'database' => $_ENV['DB_DATABASE'] ?: '',
                'username' => $_ENV['DB_USERNAME'] ?: '',
                'password' => $_ENV['DB_PASSWORD'] ?: '',
                'prefix' => $_ENV['DB_PREFIX'] ?: '',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'strict' => false,
                'engine' => null,
                'canFail' => FALSE
            ],
            'alt' => [
                'host' => $_ENV['ALT_DB_HOST'] ?: '',
                'port' => $_ENV['ALT_DB_PORT'] ?: '',
                'database' => $_ENV['ALT_DB_DATABASE'] ?: '',
                'username' => $_ENV['ALT_DB_USERNAME'] ?: '',
                'password' => $_ENV['ALT_DB_PASSWORD'] ?: '',
                'prefix' => $_ENV['ALT_DB_PREFIX'] ?: '',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'strict' => false,
                'engine' => null,
                'canFail' => TRUE
            ]
        ],
    ],
];
