<?php

namespace app\components;

use RuntimeException;
use Throwable;
use Yii;

/**
 * Шифрование секретов, которые приходится хранить в базе.
 *
 * Пароли почтовых ящиков нельзя держать открытым текстом: дамп базы или
 * SQL-инъекция сразу означали бы доступ к корпоративной почте. Ключ берётся
 * из переменной окружения APP_SECRET_KEY и в репозиторий не попадает.
 *
 * Смена ключа делает ранее сохранённые пароли нерасшифруемыми — их придётся
 * ввести заново в карточках ящиков.
 */
class Crypt
{
    /**
     * Шифрует значение. Пустая строка и null возвращаются как null,
     * чтобы «пустой пароль» не превращался в шифротекст.
     */
    public static function encrypt(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return base64_encode(Yii::$app->security->encryptByPassword($value, self::key()));
    }

    /**
     * Расшифровывает значение. Возвращает null, если данные повреждены или
     * зашифрованы другим ключом: вызывающий код должен уметь работать без
     * пароля и сообщить администратору о необходимости ввести его снова.
     */
    public static function decrypt(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $raw = base64_decode($value, true);
        if ($raw === false) {
            return null;
        }

        try {
            $decrypted = Yii::$app->security->decryptByPassword($raw, self::key());
        } catch (Throwable $exception) {
            Yii::error('Не удалось расшифровать секрет: ' . $exception->getMessage(), __METHOD__);

            return null;
        }

        return $decrypted === false ? null : $decrypted;
    }

    /**
     * Ключ шифрования.
     *
     * APP_SECRET_KEY — основной источник. Для совместимости со стендами, где
     * задан только ключ подписи cookie, допускается COOKIE_VALIDATION_KEY.
     */
    protected static function key(): string
    {
        $key = $_ENV['APP_SECRET_KEY'] ?? $_ENV['COOKIE_VALIDATION_KEY'] ?? null;

        if (empty($key)) {
            throw new RuntimeException(
                'APP_SECRET_KEY не задан. Добавьте ключ в .env — без него пароли почтовых ящиков нельзя хранить.'
            );
        }

        return (string)$key;
    }
}
