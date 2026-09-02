<?php

namespace app\components\mail;

use app\models\Mailbox;
use RuntimeException;

/**
 * Чтение писем из ящика по IMAP.
 *
 * Класс отвечает только за транспорт и разбор письма в простой массив;
 * бизнес-логика регистрации заявок живёт в IncomingMailProcessor. Такое
 * разделение позволяет тестировать обработку писем без реального IMAP-сервера.
 *
 * Используется штатное расширение php-imap. Если оно не собрано, работа
 * прекращается с понятной ошибкой, а не падением на неизвестной функции.
 */
class ImapClient
{
    /** Максимальный размер одного вложения, байт */
    const MAX_ATTACHMENT_SIZE = 10485760; // 10 МБ

    /** Максимальный суммарный размер вложений одного письма, байт */
    const MAX_ATTACHMENTS_TOTAL = 26214400; // 25 МБ

    /** Максимальное число вложений в одном письме */
    const MAX_ATTACHMENTS_COUNT = 20;

    /**
     * Расширения, которые портал не принимает.
     *
     * Исполняемые файлы и скрипты не нужны в заявке, а их хранение и раздача
     * создают лишний риск: заявитель отправит «инструкцию.exe», а сотрудник
     * скачает и запустит.
     */
    const BLOCKED_EXTENSIONS = [
        'exe', 'com', 'bat', 'cmd', 'msi', 'scr', 'pif', 'vbs', 'vbe', 'js', 'jse',
        'wsf', 'wsh', 'ps1', 'psm1', 'jar', 'app', 'dll', 'sys', 'reg',
        'php', 'phtml', 'phar', 'sh', 'bash', 'cgi', 'pl',
    ];

    /** @var Mailbox */
    protected $mailbox;

    /** @var resource|\IMAP\Connection|null */
    protected $connection;

    public function __construct(Mailbox $mailbox)
    {
        $this->mailbox = $mailbox;
    }

    /**
     * Собрано ли расширение php-imap
     * @return bool
     */
    public static function isAvailable(): bool
    {
        return extension_loaded('imap');
    }

    /**
     * Открывает соединение с ящиком
     * @throws RuntimeException
     */
    public function open(): void
    {
        if (!self::isAvailable()) {
            throw new RuntimeException('Расширение php-imap не установлено — приём почты недоступен.');
        }

        if ($this->connection !== null) {
            return;
        }

        $password = $this->mailbox->getImapPasswordPlain();
        if ($password === null) {
            throw new RuntimeException('Для ящика ' . $this->mailbox->email . ' не сохранён пароль IMAP.');
        }

        // Ошибки imap_open приходят предупреждениями, поэтому текст берём из
        // imap_last_error(): иначе администратор увидит только «false».
        $connection = @imap_open(
            $this->mailbox->getImapConnectionString(),
            $this->mailbox->imap_login,
            $password,
            0,
            1
        );

        if ($connection === false) {
            throw new RuntimeException('IMAP: ' . (imap_last_error() ?: 'не удалось подключиться к ящику'));
        }

        $this->connection = $connection;
    }

    /**
     * Закрывает соединение
     */
    public function close(): void
    {
        if ($this->connection !== null) {
            @imap_close($this->connection);
            $this->connection = null;
        }

        // Расширение накапливает предупреждения между вызовами — сбрасываем,
        // чтобы ошибки одного ящика не «прилипали» к следующему.
        if (self::isAvailable()) {
            imap_errors();
            imap_alerts();
        }
    }

    /**
     * Проверка подключения: открыть ящик и посчитать письма
     * @return int Количество писем в папке
     */
    public function test(): int
    {
        $this->open();
        $status = @imap_status($this->connection, $this->mailbox->getImapConnectionString(), SA_MESSAGES);
        $this->close();

        return $status ? (int)$status->messages : 0;
    }

    /**
     * Новые письма: всё, что появилось после сохранённого UID.
     *
     * UID монотонно растёт внутри папки, поэтому опрос не зависит от флага
     * «прочитано» и не перечитывает всю папку при каждом запуске.
     *
     * @param int $limit Максимум писем за один проход
     * @return array[] Разобранные письма
     */
    public function fetchNew(int $limit = 50): array
    {
        $this->open();

        $lastUid = (int)$this->mailbox->last_uid;
        $overview = @imap_fetch_overview($this->connection, ($lastUid + 1) . ':*', FT_UID);

        if (!is_array($overview)) {
            return [];
        }

        $messages = [];
        foreach ($overview as $item) {
            $uid = (int)($item->uid ?? 0);

            // Если новых писем нет, сервер возвращает последнее существующее —
            // такие письма нужно отбросить, иначе они обработаются повторно.
            if ($uid <= $lastUid) {
                continue;
            }

            $messages[$uid] = $uid;

            if (count($messages) >= $limit) {
                break;
            }
        }

        ksort($messages);

        $parsed = [];
        foreach ($messages as $uid) {
            $parsed[] = $this->fetchMessage($uid);
        }

        return $parsed;
    }

    /**
     * Помечает письмо прочитанным. Вызывается только после успешной
     * регистрации заявки, чтобы сбой не «съел» обращение.
     * @param int $uid
     */
    public function markSeen(int $uid): void
    {
        if ($this->connection === null) {
            return;
        }

        @imap_setflag_full($this->connection, (string)$uid, '\\Seen', ST_UID);
    }

    /**
     * Разбирает одно письмо
     * @param int $uid
     * @return array
     */
    protected function fetchMessage(int $uid): array
    {
        $rawHeaders = (string)@imap_fetchheader($this->connection, $uid, FT_UID);
        $headers = @imap_rfc822_parse_headers($rawHeaders);
        $structure = @imap_fetchstructure($this->connection, $uid, FT_UID);

        $bodies = ['text' => '', 'html' => ''];
        $attachments = [];
        $rejected = [];

        if ($structure !== false) {
            $this->collectParts($uid, $structure, '', $bodies, $attachments, $rejected);
        }

        // Файлы, которые портал не принял, упоминаются в тексте заявки:
        // сотрудник должен знать, что вложение было, и запросить его иначе.
        foreach ($rejected as $note) {
            $bodies['text'] .= "\n[вложение не сохранено: " . $note['name'] . ' — ' . $note['reason'] . ']';
        }

        $from = $this->firstAddress($headers->from ?? []);
        $to = $this->firstAddress($headers->to ?? []);

        return [
            'uid' => $uid,
            'message_id' => $this->normalizeMessageId($headers->message_id ?? null),
            'in_reply_to' => $this->normalizeMessageId($headers->in_reply_to ?? null),
            'references' => $this->parseReferences($headers->references ?? null),
            'subject' => $this->decodeHeader($headers->subject ?? ''),
            'from_email' => $from['email'],
            'from_name' => $from['name'],
            'to_email' => $to['email'],
            'date' => isset($headers->date) ? date('Y-m-d H:i:s', strtotime($headers->date)) : date('Y-m-d H:i:s'),
            'body_text' => trim($bodies['text']),
            'body_html' => trim($bodies['html']),
            'raw_headers' => $rawHeaders,
            'is_auto' => $this->looksAutomatic($rawHeaders),
            'attachments' => $attachments,
            'rejected_attachments' => $rejected,
        ];
    }

    /**
     * Рекурсивно собирает текстовые части и вложения письма.
     *
     * Содержимое вложений возвращается в памяти, поэтому действуют лимиты
     * по размеру и количеству: одно письмо с архивом на полгигабайта не должно
     * ронять приём почты по memory_limit.
     *
     * @param int $uid
     * @param object $part
     * @param string $section
     * @param array $bodies
     * @param array $attachments Собранные вложения
     * @param array $rejected Файлы, которые не приняты, с причиной
     */
    protected function collectParts(
        int $uid,
        $part,
        string $section,
        array &$bodies,
        array &$attachments = [],
        array &$rejected = []
    ): void {
        $subtype = strtolower((string)($part->subtype ?? ''));

        // Составное письмо: спускаемся во вложенные части (text/plain,
        // text/html, вложения). Нумерация секций — как требует IMAP: 1, 1.2 и т.д.
        if (!empty($part->parts)) {
            foreach ($part->parts as $index => $child) {
                $childSection = $section === '' ? (string)($index + 1) : $section . '.' . ($index + 1);
                $this->collectParts($uid, $child, $childSection, $bodies, $attachments, $rejected);
            }

            return;
        }

        $fileName = $this->partFileName($part);
        if ($fileName !== null) {
            $this->collectAttachment($uid, $part, $section, $fileName, $attachments, $rejected);

            return;
        }

        if ((int)($part->type ?? 0) !== TYPETEXT) {
            return;
        }

        $raw = (string)@imap_fetchbody($this->connection, $uid, $section === '' ? '1' : $section, FT_UID);
        $content = $this->decodeBody($raw, (int)($part->encoding ?? 0));
        $content = $this->toUtf8($content, $this->partCharset($part));

        if ($subtype === 'html') {
            $bodies['html'] .= $content;
        } else {
            $bodies['text'] .= $content;
        }
    }

    /**
     * Забирает одно вложение с учётом ограничений.
     *
     * Проверки идут до загрузки содержимого: размер известен из структуры
     * письма, и тянуть с сервера файл, который всё равно будет отброшен,
     * нет смысла.
     *
     * @param int $uid
     * @param object $part
     * @param string $section
     * @param string $fileName
     * @param array $attachments
     * @param array $rejected
     */
    protected function collectAttachment(
        int $uid,
        $part,
        string $section,
        string $fileName,
        array &$attachments,
        array &$rejected
    ): void {
        $fileName = $this->sanitizeFileName($fileName);
        $declaredSize = (int)($part->bytes ?? 0);

        if (count($attachments) >= self::MAX_ATTACHMENTS_COUNT) {
            $rejected[] = ['name' => $fileName, 'reason' => 'в письме слишком много файлов'];

            return;
        }

        if ($this->isBlockedFile($fileName)) {
            $rejected[] = ['name' => $fileName, 'reason' => 'тип файла запрещён'];

            return;
        }

        if ($declaredSize > self::MAX_ATTACHMENT_SIZE) {
            $rejected[] = [
                'name' => $fileName,
                'reason' => 'размер больше ' . round(self::MAX_ATTACHMENT_SIZE / 1048576) . ' МБ',
            ];

            return;
        }

        $raw = (string)@imap_fetchbody($this->connection, $uid, $section === '' ? '1' : $section, FT_UID);
        $content = $this->decodeBody($raw, (int)($part->encoding ?? 0));
        unset($raw);

        $size = strlen($content);

        if ($size === 0) {
            $rejected[] = ['name' => $fileName, 'reason' => 'пустой файл'];

            return;
        }

        if ($size > self::MAX_ATTACHMENT_SIZE) {
            $rejected[] = [
                'name' => $fileName,
                'reason' => 'размер больше ' . round(self::MAX_ATTACHMENT_SIZE / 1048576) . ' МБ',
            ];

            return;
        }

        $total = $size;
        foreach ($attachments as $collected) {
            $total += (int)$collected['size'];
        }

        if ($total > self::MAX_ATTACHMENTS_TOTAL) {
            $rejected[] = ['name' => $fileName, 'reason' => 'превышен суммарный размер вложений'];

            return;
        }

        $attachments[] = [
            'name' => $fileName,
            'mime' => $this->partMimeType($part),
            'size' => $size,
            'content' => $content,
            'is_inline' => strtolower((string)($part->disposition ?? '')) === 'inline',
        ];
    }

    /**
     * MIME-тип части письма по её структуре
     * @param object $part
     * @return string
     */
    protected function partMimeType($part): string
    {
        $primary = [
            TYPETEXT => 'text',
            TYPEMULTIPART => 'multipart',
            TYPEMESSAGE => 'message',
            TYPEAPPLICATION => 'application',
            TYPEAUDIO => 'audio',
            TYPEIMAGE => 'image',
            TYPEVIDEO => 'video',
            TYPEMODEL => 'model',
            TYPEOTHER => 'application',
        ];

        $type = $primary[(int)($part->type ?? TYPEOTHER)] ?? 'application';
        $subtype = strtolower((string)($part->subtype ?? 'octet-stream'));
        $subtype = preg_replace('/[^a-z0-9.+\-]/', '', $subtype);

        return mb_substr($type . '/' . ($subtype !== '' ? $subtype : 'octet-stream'), 0, 150);
    }

    /**
     * Очищает имя файла из письма.
     *
     * Имя пришло извне, поэтому из него убираются пути и управляющие
     * символы: оно показывается в интерфейсе и попадает в заголовок
     * Content-Disposition при скачивании.
     *
     * @param string $fileName
     * @return string
     */
    protected function sanitizeFileName(string $fileName): string
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
     * Запрещён ли файл по расширению (в том числе двойному вида .pdf.exe)
     * @param string $fileName
     * @return bool
     */
    protected function isBlockedFile(string $fileName): bool
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
     * Имя файла вложения, если часть письма является вложением
     * @param object $part
     * @return string|null
     */
    protected function partFileName($part): ?string
    {
        foreach (['dparameters', 'parameters'] as $bag) {
            if (empty($part->$bag)) {
                continue;
            }

            foreach ($part->$bag as $parameter) {
                if (in_array(strtolower((string)$parameter->attribute), ['filename', 'name'], true)) {
                    return $this->decodeHeader((string)$parameter->value);
                }
            }
        }

        return null;
    }

    /**
     * Кодировка части письма
     * @param object $part
     * @return string
     */
    protected function partCharset($part): string
    {
        if (!empty($part->parameters)) {
            foreach ($part->parameters as $parameter) {
                if (strtolower((string)$parameter->attribute) === 'charset') {
                    return (string)$parameter->value;
                }
            }
        }

        return 'UTF-8';
    }

    /**
     * Раскодирует тело части по transfer-encoding
     * @param string $body
     * @param int $encoding
     * @return string
     */
    protected function decodeBody(string $body, int $encoding): string
    {
        switch ($encoding) {
            case ENCBASE64:
                return (string)base64_decode($body, false);
            case ENCQUOTEDPRINTABLE:
                return quoted_printable_decode($body);
            default:
                return $body;
        }
    }

    /**
     * Приводит текст к UTF-8
     * @param string $value
     * @param string $charset
     * @return string
     */
    protected function toUtf8(string $value, string $charset): string
    {
        $charset = strtoupper(trim($charset));

        if ($charset === '' || $charset === 'UTF-8' || $charset === 'US-ASCII') {
            return $value;
        }

        $converted = @iconv($charset, 'UTF-8//TRANSLIT', $value);

        return $converted === false ? $value : $converted;
    }

    /**
     * Раскодирует MIME-заголовок (=?UTF-8?B?...?=)
     * @param string $value
     * @return string
     */
    public function decodeHeader(string $value): string
    {
        if ($value === '') {
            return '';
        }

        $decoded = @iconv_mime_decode($value, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8');

        return $decoded === false ? $value : trim($decoded);
    }

    /**
     * Первый адрес из списка адресов заголовка
     * @param array $addresses
     * @return array{email: string|null, name: string|null}
     */
    protected function firstAddress($addresses): array
    {
        if (empty($addresses) || !is_array($addresses)) {
            return ['email' => null, 'name' => null];
        }

        $address = reset($addresses);
        $email = null;

        if (!empty($address->mailbox) && !empty($address->host)) {
            $email = strtolower($address->mailbox . '@' . $address->host);
        }

        return [
            'email' => $email,
            'name' => isset($address->personal) ? $this->decodeHeader((string)$address->personal) : null,
        ];
    }

    /**
     * Приводит Message-ID к виду без угловых скобок
     * @param string|null $value
     * @return string|null
     */
    protected function normalizeMessageId(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        $value = trim($value, " \t\n\r<>");

        return $value === '' ? null : mb_substr($value, 0, 255);
    }

    /**
     * Список идентификаторов из заголовка References
     * @param string|null $value
     * @return string[]
     */
    protected function parseReferences(?string $value): array
    {
        if (empty($value)) {
            return [];
        }

        preg_match_all('/<([^>]+)>/', $value, $matches);

        return array_map(function ($id) {
            return mb_substr(trim($id), 0, 255);
        }, $matches[1] ?? []);
    }

    /**
     * Похоже ли письмо на автоответ или рассылку.
     *
     * Такие письма нельзя превращать в заявки и тем более отвечать на них:
     * автоответчик на нашей стороне и автоответчик на стороне клиента
     * образуют бесконечный цикл писем.
     *
     * @param string $rawHeaders
     * @return bool
     */
    protected function looksAutomatic(string $rawHeaders): bool
    {
        $headers = strtolower($rawHeaders);

        if (preg_match('/^auto-submitted:\s*(?!no)/mi', $headers)) {
            return true;
        }

        if (preg_match('/^precedence:\s*(bulk|list|junk)/mi', $headers)) {
            return true;
        }

        foreach (['x-autoreply', 'x-autorespond', 'x-mailer-daemon', 'list-unsubscribe'] as $marker) {
            if (strpos($headers, "\n" . $marker . ':') !== false || strncmp($headers, $marker . ':', strlen($marker) + 1) === 0) {
                return true;
            }
        }

        return false;
    }
}
