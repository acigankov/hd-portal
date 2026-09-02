<?php

namespace app\components\mail;

/**
 * Общие правила для вложений заявок.
 *
 * Ограничения одни и те же для входящих писем и для файлов, которые сотрудник
 * прикладывает к ответу: иначе получилось бы, что портал не принимает файл
 * от заявителя, но сам отправляет такой же.
 */
class AttachmentPolicy
{
    /** Максимальный размер одного файла, байт */
    const MAX_FILE_SIZE = 10485760; // 10 МБ

    /** Максимальный суммарный размер файлов одного сообщения, байт */
    const MAX_TOTAL_SIZE = 26214400; // 25 МБ

    /** Максимальное число файлов в одном сообщении */
    const MAX_COUNT = 20;

    /**
     * Расширения, которые портал не принимает и не отправляет.
     *
     * Исполняемые файлы и скрипты не нужны в переписке по заявке, а их
     * хранение и раздача создают лишний риск: заявитель отправит
     * «инструкцию.exe», а сотрудник скачает и запустит.
     */
    const BLOCKED_EXTENSIONS = [
        'exe', 'com', 'bat', 'cmd', 'msi', 'scr', 'pif', 'vbs', 'vbe', 'js', 'jse',
        'wsf', 'wsh', 'ps1', 'psm1', 'jar', 'app', 'dll', 'sys', 'reg',
        'php', 'phtml', 'phar', 'sh', 'bash', 'cgi', 'pl',
    ];

    /**
     * Запрещён ли файл по расширению.
     *
     * Проверяется каждая часть имени, поэтому «счёт.pdf.exe» тоже не пройдёт.
     *
     * @param string $fileName
     * @return bool
     */
    public static function isBlocked(string $fileName): bool
    {
        $parts = array_map('strtolower', explode('.', $fileName));
        array_shift($parts);

        foreach ($parts as $extension) {
            if (in_array($extension, self::BLOCKED_EXTENSIONS, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Очищает имя файла, пришедшее извне.
     *
     * Из имени убираются пути и управляющие символы: оно показывается
     * в интерфейсе, попадает в заголовок Content-Disposition при скачивании
     * и в имя вложения исходящего письма.
     *
     * @param string $fileName
     * @return string
     */
    public static function sanitizeFileName(string $fileName): string
    {
        $fileName = str_replace(['\\', '/'], '_', $fileName);
        $fileName = preg_replace('/[\x00-\x1F\x7F"]+/u', '', $fileName);
        $fileName = trim((string)$fileName, " .\t");

        if ($fileName === '') {
            $fileName = 'вложение';
        }

        return mb_substr($fileName, 0, 255);
    }

    /**
     * Максимальный размер файла в мегабайтах — для текстов в интерфейсе
     * @return int
     */
    public static function maxFileSizeMb(): int
    {
        return (int)round(self::MAX_FILE_SIZE / 1048576);
    }

    /**
     * Расширения без точки для подсказок и валидаторов
     * @return string[]
     */
    public static function blockedExtensions(): array
    {
        return self::BLOCKED_EXTENSIONS;
    }
}
