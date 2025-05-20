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
    'jwt_secret' => getenv('JWT_SECRET'),
    'urlrender' => getenv('URL_RENDER')
];
