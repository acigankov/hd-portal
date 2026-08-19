<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'language' => 'ru-RU',
    'sourceLanguage' => 'en-US',
    'timeZone' => 'Europe/Moscow', // укажите свой часовой пояс
    'bootstrap' => ['log', 'debug'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'on beforeRequest' => function ($event) {
        $session = Yii::$app->session;
        if ($session->has('language')) {
            Yii::$app->language = $session->get('language');
        }
    },
    'modules' => [
        'debug' => [
            'class' => 'yii\debug\Module',
            // Опционально: ограничение по IP
            'allowedIPs' => ['*'],
        ],
        'rbac' => [
            'class' => 'app\modules\rbac\Module',
        ],

    ],
    'components' => [
        'formatter' => [
            'class' => 'yii\i18n\Formatter',
            'dateFormat' => 'php:d.m.Y',
            'timeFormat' => 'php:H:i:s',
            'datetimeFormat' => 'dd.MM.yyyy HH:mm:ss',
        ],
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'Vi9dMsXZ4Vz2DROQZsOtK2KoOJJDsAcr',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => false,
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
            'defaultRoles' => ['guest', 'user'],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            // send all mails to a file by default.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                '' => 'site/index',
                'login' => 'site/login',
                'status' => 'status/index',
                'status/<id:\d+>' => 'status/view',
                'status/create' => 'status/create',
                'status/update/<id:\d+>' => 'status/update',
                'status/delete/<id:\d+>' => 'status/delete',
            ],
        ],
        'i18n' => [
            'translations' => [
                'yii2mod.rbac' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    // Путь к файлам переводов в vendor
                    'basePath' => '@vendor/yii2mod/yii2-rbac/messages',

                ],
                'app*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@app/messages',
                    'fileMap' => [
                        'app' => 'app.php',
                    ],
                ],
            ],
        ],

    ],
    // ограничить доступ к приложению Yii2 только для авторизованных пользователей до инициализации контроллеров
    'as beforeRequest' => [
        'class' => \yii\filters\AccessControl::class,
        'rules' => [
            [
                'actions' => ['login', 'signup', 'request-password-reset', 'error', 'set-language'],
                'allow' => true,
                'roles' => ['?', '@'], // Разрешить всем (и гостям, и авторизованным)
            ],
            [
                'allow' => true,
                'roles' => ['@'], // Все остальные — только для авторизованных
            ],
            [
                'allow' => false,
                'roles' => ['?'],
                'denyCallback' => function ($rule, $action) {
                    return $action->controller->redirect(['site/login'])->send();
                },
            ],
        ],
        'denyCallback' => function ($rule, $action) {
            return Yii::$app->response->redirect(['site/login']);
        },
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['*', '::1'], // или ваш IP-адрес
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['*', '::1'],
    ];
}

return $config;
