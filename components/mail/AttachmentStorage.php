<?php

namespace app\components\mail;

use RuntimeException;
use Yii;

/**
 * Хранилище вложений входящих писем.
 *
 * Файлы кладутся в каталог вне web-корня (@runtime/mail-attachments по
 * умолчанию), поэтому скачать вложение можно только через контроллер портала,
 * который проверяет права. Имя файла на диске генерируется случайно: письмо
 * может содержать имя вида «../../config/db.php» или «счёт.pdf.exe», и такое
 * имя не должно попадать в файловую систему.
 *
 * Раскладка по каталогам «ящик / год / месяц» не даёт вырасти одной папке
 * до сотен тысяч файлов, где замедляется любая операция с директорией.
 */
class AttachmentStorage
{
    /** @var string Корневой каталог хранилища */
    protected $basePath;

    /**
     * @param string|null $basePath Путь или алиас Yii; по умолчанию @runtime/mail-attachments
     */
    public function __construct(?string $basePath = null)
    {
        $path = $basePath ?? (string)Yii::getAlias('@runtime/mail-attachments');
        $this->basePath = rtrim($path, DIRECTORY_SEPARATOR);
    }

    /**
     * Корневой каталог хранилища
     * @return string
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * Сохраняет содержимое вложения
     *
     * @param int $mailboxId
     * @param string $content Двоичное содержимое файла
     * @param string $originalName Имя файла из письма — используется только для расширения
     * @return string Путь относительно корня хранилища
     * @throws RuntimeException
     */
    public function save(int $mailboxId, string $content, string $originalName): string
    {
        $relativeDir = $mailboxId . '/' . date('Y') . '/' . date('m');
        $absoluteDir = $this->basePath . '/' . $relativeDir;

        if (!is_dir($absoluteDir) && !@mkdir($absoluteDir, 0775, true) && !is_dir($absoluteDir)) {
            throw new RuntimeException('Не удалось создать каталог для вложений: ' . $absoluteDir);
        }

        $relativePath = $relativeDir . '/' . bin2hex(random_bytes(16)) . $this->safeExtension($originalName);

        if (@file_put_contents($this->basePath . '/' . $relativePath, $content) === false) {
            throw new RuntimeException('Не удалось сохранить вложение в ' . $relativePath);
        }

        @chmod($this->basePath . '/' . $relativePath, 0640);

        return $relativePath;
    }

    /**
     * Абсолютный путь к сохранённому файлу.
     *
     * Путь из базы проверяется на выход за пределы хранилища: даже если
     * запись в БД будет испорчена, отдать /etc/passwd не получится.
     *
     * @param string $relativePath
     * @return string|null null, если файла нет или путь некорректен
     */
    public function resolve(string $relativePath): ?string
    {
        if ($relativePath === '' || strpos($relativePath, '..') !== false) {
            return null;
        }

        $absolute = realpath($this->basePath . '/' . $relativePath);
        $base = realpath($this->basePath);

        if ($absolute === false || $base === false || strpos($absolute, $base . DIRECTORY_SEPARATOR) !== 0) {
            return null;
        }

        return is_file($absolute) ? $absolute : null;
    }

    /**
     * Удаляет файл вложения
     * @param string $relativePath
     * @return bool
     */
    public function delete(string $relativePath): bool
    {
        $absolute = $this->resolve($relativePath);

        return $absolute !== null ? @unlink($absolute) : false;
    }

    /**
     * Безопасное расширение файла.
     *
     * Берём только последнее расширение и только из букв и цифр — так
     * «отчёт.pdf» останется .pdf, а «шелл.php;.jpg» не превратится в
     * исполняемый файл.
     *
     * @param string $originalName
     * @return string Расширение с точкой или пустая строка
     */
    protected function safeExtension(string $originalName): string
    {
        $extension = (string)pathinfo($originalName, PATHINFO_EXTENSION);
        $extension = strtolower(preg_replace('/[^a-z0-9]/i', '', $extension));

        if ($extension === '' || strlen($extension) > 10) {
            return '.bin';
        }

        // Файл всё равно лежит вне web-корня и отдаётся только через
        // контроллер, но исполняемое расширение на диске лучше не создавать.
        $dangerous = ['php', 'phtml', 'phar', 'php3', 'php4', 'php5', 'php7', 'php8', 'cgi', 'pl', 'sh'];

        return in_array($extension, $dangerous, true) ? '.bin' : '.' . $extension;
    }
}
