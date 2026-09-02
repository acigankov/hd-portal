<?php

require __DIR__ . '/env.php';

foreach (['MYSQL_DSN', 'MYSQL_USER', 'MYSQL_USER_PASSWORD'] as $required) {
    if (!isset($_ENV[$required]) || $_ENV[$required] === '') {
        throw new RuntimeException(
            "Переменная окружения {$required} не задана. Скопируйте .env.example в .env и заполните его."
        );
    }
}

return [
    'class' => 'yii\db\Connection',
    'dsn' => $_ENV['MYSQL_DSN'],
    'username' => $_ENV['MYSQL_USER'],
    'password' => $_ENV['MYSQL_USER_PASSWORD'],
    'charset' => $_ENV['MYSQL_CHARSET'] ?? 'utf8mb4',

    // Schema cache options (for production environment)
    'enableSchemaCache' => !(defined('YII_DEBUG') && YII_DEBUG),
    'schemaCacheDuration' => 3600,
    'schemaCache' => 'cache',
];
