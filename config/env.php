<?php

/**
 * Загрузка переменных окружения из .env.
 *
 * Подключается из точек входа (web/index.php, yii, web/index-test.php)
 * до определения констант YII_DEBUG / YII_ENV, а также из config/db.php.
 * Повторный вызов безопасен: загрузка выполняется один раз.
 */

use Dotenv\Dotenv;

if (!function_exists('env_bool')) {
    /**
     * Приводит переменную окружения к bool.
     * Значения '1', 'true', 'on', 'yes' считаются истиной.
     */
    function env_bool(string $name, bool $default = false): bool
    {
        if (!array_key_exists($name, $_ENV)) {
            return $default;
        }

        return filter_var($_ENV[$name], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $default;
    }
}

if (!function_exists('env_list')) {
    /**
     * Читает список значений, разделённых запятой (например, список IP).
     *
     * @return string[]
     */
    function env_list(string $name, array $default = []): array
    {
        if (empty($_ENV[$name])) {
            return $default;
        }

        $values = array_filter(array_map('trim', explode(',', (string)$_ENV[$name])), 'strlen');

        return $values !== [] ? array_values($values) : $default;
    }
}

if (!defined('APP_ENV_LOADED')) {
    Dotenv::createImmutable(dirname(__DIR__))->safeLoad();
    define('APP_ENV_LOADED', true);
}
