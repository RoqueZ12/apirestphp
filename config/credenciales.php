<?php
return [
    'db' => [
        'host' => getenv('DB_HOST'),
        'name' => getenv('DB_NAME'),
        'user' => getenv('DB_USER'),
        'pass' => getenv('DB_PASS'),
        'port' => getenv('DB_PORT'),
    ],
    'firebase_credentials_json' => getenv('FIREBASE_CREDENTIALS_JSON'),
    'jwt_secret' => trim($_ENV['JWT_SECRET'] ?? $_SERVER['JWT_SECRET'] ?? getenv('JWT_SECRET') ?? 'Prueba_tecnica'),
    'urlrender' => getenv('URL_RENDER')
];
