<?php

require __DIR__ . '/../vendor/autoload.php';

// Переменные окружения нужны до определения YII_DEBUG / YII_ENV,
// поэтому .env загружается здесь, а не только в config/db.php
require __DIR__ . '/../config/env.php';

// Режим отладки и окружение задаются в .env, а не в коде.
// Значения по умолчанию — безопасные (прод, отладка выключена).
defined('YII_DEBUG') or define('YII_DEBUG', env_bool('YII_DEBUG', false));
defined('YII_ENV') or define('YII_ENV', $_ENV['YII_ENV'] ?? 'prod');

require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/../config/web.php';

(new yii\web\Application($config))->run();
