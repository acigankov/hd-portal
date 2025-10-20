<?php

// Функция-помощник для получения переменных
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

return [
    'class' => 'yii\db\Connection',
    'dsn' => $_ENV['MYSQL_DSN'],
    'username' => $_ENV['MYSQL_USER'],
    'password' => $_ENV['MYSQL_USER_PASSWORD'],
    'charset' => $_ENV['MYSQL_CHARSET'],

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',

];
