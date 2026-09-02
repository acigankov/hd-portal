<?php

require __DIR__ . '/env.php';

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

// Список IP, которым разрешены dev-инструменты (Debug, Gii).
// Задаётся через DEV_TOOLS_ALLOWED_IPS в .env, по умолчанию — только localhost.
$devToolsAllowedIps = env_list('DEV_TOOLS_ALLOWED_IPS', ['127.0.0.1', '::1']);

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'language' => 'ru-RU',
    'sourceLanguage' => 'en-US',
    'timeZone' => 'Europe/Moscow', // укажите свой часовой пояс
    'bootstrap' => ['log'],
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
        // Debug и Gii подключаются только в dev-окружении, см. конец файла
        'rbac' => [
            'class' => 'app\modules\rbac\Module',
            // Управление пользователями, ролями и правами — только для админов.
            // Фильтр навешен на модуль, поэтому покрывает все его контроллеры.
            'as access' => [
                'class' => \yii\filters\AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['admin'],
                    ],
                ],
            ],
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
            // Ключ хранится только в .env. Никогда не коммитить его в репозиторий.
            'cookieValidationKey' => $_ENV['COOKIE_VALIDATION_KEY']
                ?? throw new RuntimeException(
                    'COOKIE_VALIDATION_KEY не задан. Сгенерируйте ключ и добавьте его в .env.'
                ),
            'csrfCookie' => [
                'httpOnly' => true,
                'sameSite' => \yii\web\Cookie::SAME_SITE_LAX,
            ],
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
                'task' => 'task/index',
                'task/<id:\d+>' => 'task/view',
                'task/create' => 'task/create',
                'task/update/<id:\d+>' => 'task/update',
                'task/delete/<id:\d+>' => 'task/delete',
                'ticket' => 'ticket/index',
                'ticket/create' => 'ticket/create',
                'ticket/<id:\d+>' => 'ticket/view',
                'ticket/update/<id:\d+>' => 'ticket/update',
                'ticket/delete/<id:\d+>' => 'ticket/delete',
                'ticket/reply/<id:\d+>' => 'ticket/reply',
                'ticket/change-status/<id:\d+>' => 'ticket/change-status',
                'mailbox' => 'mailbox/index',
                'mailbox/create' => 'mailbox/create',
                'mailbox/<id:\d+>' => 'mailbox/view',
                'mailbox/update/<id:\d+>' => 'mailbox/update',
                'mailbox/delete/<id:\d+>' => 'mailbox/delete',
                'mailbox/test-connection/<id:\d+>' => 'mailbox/test-connection',
                'mailbox/fetch-now/<id:\d+>' => 'mailbox/fetch-now',
                'category' => 'category/index',
                'category/<id:\d+>' => 'category/view',
                'category/create' => 'category/create',
                'category/update/<id:\d+>' => 'category/update',
                'category/delete/<id:\d+>' => 'category/delete',
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
    // Debug-панель и Gii доступны только в dev-окружении и только с разрешённых IP.
    // Gii умеет создавать файлы на сервере, поэтому '*' здесь недопустим.
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        'allowedIPs' => $devToolsAllowedIps,
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'allowedIPs' => $devToolsAllowedIps,
    ];
}

return $config;
