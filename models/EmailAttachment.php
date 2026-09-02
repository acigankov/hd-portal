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
 * @property int|null $email_message_id Входящее письмо, если файл пришёл по почте
 * @property int|null $reply_id Запись обсуждения, если файл приложил сотрудник
 * @property string $original_name Имя файла из письма
 * @property string|null $mime_type Тип содержимого
 * @property int $size Размер в байтах
 * @property string $storage_path Путь относительно каталога вложений
 * @property string|null $checksum SHA-256 содержимого
 * @property bool $is_inline Встроенное изображение из HTML-письма
 * @property string $created_at
 *
 * @property EmailMessage|null $emailMessage
 * @property TicketReply|null $reply
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
            [['original_name', 'storage_path'], 'required'],
            [['email_message_id', 'reply_id', 'size'], 'integer'],
            // Файл без владельца был бы мусором в хранилище: его нельзя
            // ни показать в заявке, ни удалить вместе с ней.
            [['email_message_id'], 'required', 'when' => function (self $model) {
                return empty($model->reply_id);
            }, 'message' => 'Вложение должно быть связано с письмом или с ответом.'],
            [['is_inline'], 'boolean'],
            [['original_name', 'storage_path'], 'string', 'max' => 255],
            [['mime_type'], 'string', 'max' => 150],
            [['checksum'], 'string', 'max' => 64],
            [['created_at'], 'safe'],
            [['email_message_id'], 'exist', 'skipOnEmpty' => true, 'targetClass' => EmailMessage::class, 'targetAttribute' => 'id'],
            [['reply_id'], 'exist', 'skipOnEmpty' => true, 'targetClass' => TicketReply::class, 'targetAttribute' => 'id'],
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
     * Запись обсуждения, если файл приложил сотрудник
     * @return \yii\db\ActiveQuery
     */
    public function getReply()
    {
        return $this->hasOne(TicketReply::class, ['id' => 'reply_id']);
    }

    /**
     * Файлы, приложенные к ответу сотрудника — их надо вложить в письмо
     *
     * @param int $replyId
     * @return self[]
     */
    public static function forReply(int $replyId): array
    {
        return self::find()
            ->andWhere(['reply_id' => $replyId])
            ->orderBy(['id' => SORT_ASC])
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
        $incomingIds = EmailMessage::find()
            ->select('id')
            ->andWhere(['ticket_id' => $ticketId, 'direction' => EmailMessage::DIRECTION_INCOMING]);

        $replyIds = TicketReply::find()
            ->select('id')
            ->andWhere(['ticket_id' => $ticketId]);

        $rows = self::find()
            ->andWhere(['or', ['email_message_id' => $incomingIds], ['reply_id' => $replyIds]])
            ->with('emailMessage')
            ->orderBy(['id' => SORT_ASC])
            ->all();

        $grouped = [];
        foreach ($rows as $row) {
            // Файл сотрудника привязан к ответу напрямую, файл из письма —
            // через запись журнала; у первого письма записи обсуждения нет.
            $key = $row->reply_id !== null
                ? (int)$row->reply_id
                : (int)($row->emailMessage->reply_id ?? 0);

            $grouped[$key][] = $row;
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
