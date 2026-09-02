<?php

namespace app\models;

use app\components\mail\AttachmentStorage;
use yii\db\ActiveRecord;

/**
 * Вложение входящего письма.
 *
 * Запись всегда принадлежит письму из журнала email_messages, а сам файл
 * лежит в каталоге вне web-корня. В интерфейсе вложение доступно только
 * через действие скачивания, которое проверяет права пользователя.
 *
 * @property int $id
 * @property int $email_message_id Письмо, к которому приложен файл
 * @property string $original_name Имя файла из письма
 * @property string|null $mime_type Тип содержимого
 * @property int $size Размер в байтах
 * @property string $storage_path Путь относительно каталога вложений
 * @property string|null $checksum SHA-256 содержимого
 * @property bool $is_inline Встроенное изображение из HTML-письма
 * @property string $created_at
 *
 * @property EmailMessage $emailMessage
 */
class EmailAttachment extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%email_attachments}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['email_message_id', 'original_name', 'storage_path'], 'required'],
            [['email_message_id', 'size'], 'integer'],
            [['is_inline'], 'boolean'],
            [['original_name', 'storage_path'], 'string', 'max' => 255],
            [['mime_type'], 'string', 'max' => 150],
            [['checksum'], 'string', 'max' => 64],
            [['created_at'], 'safe'],
            [['email_message_id'], 'exist', 'targetClass' => EmailMessage::class, 'targetAttribute' => 'id'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'original_name' => 'Имя файла',
            'mime_type' => 'Тип файла',
            'size' => 'Размер',
            'is_inline' => 'Встроенное изображение',
            'created_at' => 'Сохранено',
        ];
    }

    /**
     * Письмо, к которому приложен файл
     * @return \yii\db\ActiveQuery
     */
    public function getEmailMessage()
    {
        return $this->hasOne(EmailMessage::class, ['id' => 'email_message_id']);
    }

    /**
     * Вложения заявки: и из первого письма, и из ответов заявителя
     *
     * @param int $ticketId
     * @return self[]
     */
    public static function forTicket(int $ticketId): array
    {
        return self::find()
            ->alias('a')
            ->innerJoin(['m' => EmailMessage::tableName()], 'm.id = a.email_message_id')
            ->andWhere(['m.ticket_id' => $ticketId])
            ->orderBy(['a.id' => SORT_ASC])
            ->all();
    }

    /**
     * Вложения, сгруппированные по записям обсуждения.
     *
     * Ключ null означает вложения исходного письма, из которого создана
     * заявка: у него ещё нет записи в обсуждении.
     *
     * @param int $ticketId
     * @return array<int|string, self[]>
     */
    public static function groupedByReply(int $ticketId): array
    {
        $messages = EmailMessage::find()
            ->andWhere(['ticket_id' => $ticketId, 'direction' => EmailMessage::DIRECTION_INCOMING])
            ->with('attachments')
            ->orderBy(['id' => SORT_ASC])
            ->all();

        $grouped = [];
        foreach ($messages as $message) {
            if (empty($message->attachments)) {
                continue;
            }

            $key = $message->reply_id === null ? 0 : (int)$message->reply_id;
            foreach ($message->attachments as $attachment) {
                $grouped[$key][] = $attachment;
            }
        }

        return $grouped;
    }

    /**
     * Абсолютный путь к файлу или null, если файл потерян
     * @return string|null
     */
    public function getAbsolutePath(): ?string
    {
        return (new AttachmentStorage())->resolve($this->storage_path);
    }

    /**
     * Читаемый размер файла
     * @return string
     */
    public function getFormattedSize(): string
    {
        $size = (int)$this->size;

        if ($size < 1024) {
            return $size . ' Б';
        }

        if ($size < 1024 * 1024) {
            return round($size / 1024) . ' КБ';
        }

        return round($size / (1024 * 1024), 1) . ' МБ';
    }

    /**
     * Иконка Bootstrap Icons по типу файла — помогает быстро найти нужный файл
     * @return string
     */
    public function getIconClass(): string
    {
        $mime = strtolower((string)$this->mime_type);
        $extension = strtolower((string)pathinfo((string)$this->original_name, PATHINFO_EXTENSION));

        if (strpos($mime, 'image/') === 0) {
            return 'bi bi-file-earmark-image';
        }

        if ($mime === 'application/pdf' || $extension === 'pdf') {
            return 'bi bi-file-earmark-pdf';
        }

        if (in_array($extension, ['doc', 'docx', 'rtf', 'odt'], true)) {
            return 'bi bi-file-earmark-word';
        }

        if (in_array($extension, ['xls', 'xlsx', 'csv', 'ods'], true)) {
            return 'bi bi-file-earmark-spreadsheet';
        }

        if (in_array($extension, ['zip', 'rar', '7z', 'tar', 'gz'], true)) {
            return 'bi bi-file-earmark-zip';
        }

        return 'bi bi-file-earmark';
    }

    /**
     * Файлы удаляются вместе с записью, иначе хранилище будет расти вечно
     * @return bool
     */
    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }

        (new AttachmentStorage())->delete($this->storage_path);

        return true;
    }
}
