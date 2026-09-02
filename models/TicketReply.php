<?php

namespace app\models;

use app\components\mail\AttachmentPolicy;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;

/**
 * Запись обсуждения по заявке: ответ специалиста, ответ заявителя или
 * системная отметка о смене статуса.
 *
 * @property int $id
 * @property int $ticket_id ID заявки
 * @property string $type Тип записи: reply | system
 * @property string $author_side Сторона: operator | client
 * @property int|null $user_id ID пользователя (ответ специалиста)
 * @property int|null $author_id ID заявителя из справочника
 * @property string|null $author_name Подпись автора ответа
 * @property string|null $text Текст ответа
 * @property int|null $status_from_id Прежний статус (системная запись)
 * @property int|null $status_to_id Новый статус (системная запись)
 * @property bool $is_public Ответ заявителю (1) или внутренняя заметка (0)
 * @property string|null $email_status Состояние отправки письма: queued | sent | failed
 * @property string|null $email_sent_at Когда письмо ушло заявителю
 * @property string $created_at Дата создания
 * @property string|null $updated_at Дата изменения
 *
 * @property Ticket $ticket
 * @property User|null $user
 * @property Author|null $author
 * @property Status|null $statusFrom
 * @property Status|null $statusTo
 * @property string $displayName
 * @property bool $isSystem
 * @property bool $isFromOperator
 */
class TicketReply extends ActiveRecord
{
    const TYPE_REPLY = 'reply';
    const TYPE_SYSTEM = 'system';

    const SIDE_OPERATOR = 'operator';
    const SIDE_CLIENT = 'client';

    // Состояния отправки ответа заявителю по email
    const EMAIL_QUEUED = 'queued';
    const EMAIL_SENT = 'sent';
    const EMAIL_FAILED = 'failed';

    /**
     * Файлы из формы ответа.
     *
     * Не хранится в таблице: после сохранения ответа файлы переезжают
     * в хранилище вложений и записываются в email_attachments.
     *
     * @var \yii\web\UploadedFile[]
     */
    public $uploadedFiles = [];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%ticket_replies}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    BaseActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                    BaseActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
                'value' => function () {
                    return date('Y-m-d H:i:s');
                },
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ticket_id'], 'required'],
            [['ticket_id', 'user_id', 'author_id', 'status_from_id', 'status_to_id'], 'integer'],
            [['type'], 'default', 'value' => self::TYPE_REPLY],
            [['type'], 'in', 'range' => [self::TYPE_REPLY, self::TYPE_SYSTEM]],
            [['author_side'], 'default', 'value' => self::SIDE_OPERATOR],
            [['author_side'], 'in', 'range' => [self::SIDE_OPERATOR, self::SIDE_CLIENT]],
            [['author_name'], 'string', 'max' => 255],
            [['is_public'], 'boolean'],
            [['is_public'], 'default', 'value' => false],
            [['email_status'], 'in', 'range' => [self::EMAIL_QUEUED, self::EMAIL_SENT, self::EMAIL_FAILED]],
            [['email_sent_at'], 'safe'],
            [['text'], 'string', 'max' => 20000],
            // Текст обязателен только для ответов: системные записи описываются
            // парой статусов и рисуются шаблоном.
            [['text'], 'required', 'when' => function (self $model) {
                return $model->type !== self::TYPE_SYSTEM;
            }, 'whenClient' => false, 'message' => 'Введите текст ответа'],
            [
                ['uploadedFiles'],
                'file',
                'skipOnEmpty' => true,
                'maxFiles' => AttachmentPolicy::MAX_COUNT,
                'maxSize' => AttachmentPolicy::MAX_FILE_SIZE,
                // Исполняемые файлы не принимаются ни от заявителя по почте,
                // ни от сотрудника через форму ответа.
                'checkExtensionByMimeType' => false,
                'tooBig' => 'Файл больше ' . AttachmentPolicy::maxFileSizeMb() . ' МБ.',
                'tooMany' => 'Можно приложить не более ' . AttachmentPolicy::MAX_COUNT . ' файлов.',
            ],
            [['uploadedFiles'], 'validateUploadedFiles'],
            [['ticket_id'], 'exist', 'targetClass' => Ticket::class, 'targetAttribute' => 'id'],
            [['user_id'], 'exist', 'targetClass' => User::class, 'targetAttribute' => 'id', 'skipOnEmpty' => true],
            [['author_id'], 'exist', 'targetClass' => Author::class, 'targetAttribute' => 'id', 'skipOnEmpty' => true],
        ];
    }

    /**
     * Проверяет типы и суммарный размер приложенных файлов
     * @param string $attribute
     */
    public function validateUploadedFiles($attribute): void
    {
        $files = is_array($this->$attribute) ? $this->$attribute : [];
        $total = 0;

        foreach ($files as $file) {
            if (AttachmentPolicy::isBlocked((string)$file->name)) {
                $this->addError($attribute, 'Файл «' . $file->name . '» запрещённого типа.');

                continue;
            }

            $total += (int)$file->size;
        }

        if ($total > AttachmentPolicy::MAX_TOTAL_SIZE) {
            $this->addError(
                $attribute,
                'Суммарный размер файлов больше ' . round(AttachmentPolicy::MAX_TOTAL_SIZE / 1048576) . ' МБ.'
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ticket_id' => 'Заявка',
            'type' => 'Тип записи',
            'author_side' => 'Сторона',
            'user_id' => 'Специалист',
            'author_id' => 'Заявитель',
            'author_name' => 'Автор ответа',
            'text' => 'Текст ответа',
            'status_from_id' => 'Прежний статус',
            'status_to_id' => 'Новый статус',
            'is_public' => 'Отправить заявителю',
            'email_status' => 'Состояние отправки',
            'email_sent_at' => 'Письмо отправлено',
            'uploadedFiles' => 'Файлы',
            'created_at' => 'Создан',
            'updated_at' => 'Изменён',
        ];
    }

    /**
     * Заявка
     * @return \yii\db\ActiveQuery
     */
    public function getTicket()
    {
        return $this->hasOne(Ticket::class, ['id' => 'ticket_id']);
    }

    /**
     * Специалист, написавший ответ
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Заявитель из справочника
     * @return \yii\db\ActiveQuery
     */
    public function getAuthor()
    {
        return $this->hasOne(Author::class, ['id' => 'author_id']);
    }

    /**
     * Прежний статус
     * @return \yii\db\ActiveQuery
     */
    public function getStatusFrom()
    {
        return $this->hasOne(Status::class, ['id' => 'status_from_id']);
    }

    /**
     * Новый статус
     * @return \yii\db\ActiveQuery
     */
    public function getStatusTo()
    {
        return $this->hasOne(Status::class, ['id' => 'status_to_id']);
    }

    /**
     * Системная ли запись
     * @return bool
     */
    public function getIsSystem(): bool
    {
        return $this->type === self::TYPE_SYSTEM;
    }

    /**
     * Ответ со стороны специалиста
     * @return bool
     */
    public function getIsFromOperator(): bool
    {
        return $this->author_side === self::SIDE_OPERATOR;
    }

    /**
     * Подпись под сообщением
     * @return string
     */
    public function getDisplayName(): string
    {
        if (!empty($this->author_name)) {
            return $this->author_name;
        }

        if ($this->user_id && $this->user !== null) {
            return self::userName($this->user);
        }

        if ($this->author_id && $this->author !== null) {
            return (string)$this->author->full_name;
        }

        return $this->getIsFromOperator() ? 'Специалист' : 'Заявитель';
    }

    /**
     * Читаемое имя пользователя: имя, иначе логин
     * @param User $user
     * @return string
     */
    public static function userName(User $user): string
    {
        return !empty($user->name) ? (string)$user->name : (string)$user->login;
    }

    /**
     * Читаемое имя текущего пользователя
     * @return string|null
     */
    public static function currentUserName(): ?string
    {
        $identity = Yii::$app->user->identity;

        return $identity instanceof User ? self::userName($identity) : null;
    }

    /**
     * Видимость записи для заявителя.
     *
     * Ответом заявителю считается только явно помеченная запись: по умолчанию
     * комментарий сотрудника остаётся внутренней заметкой, чтобы служебное
     * обсуждение не уехало клиенту по ошибке.
     *
     * @return bool
     */
    public function getIsPublicReply(): bool
    {
        return (bool)$this->is_public && !$this->getIsSystem();
    }

    /**
     * Читаемое состояние отправки письма
     * @return string|null
     */
    public function getEmailStatusLabel(): ?string
    {
        switch ($this->email_status) {
            case self::EMAIL_QUEUED:
                return 'в очереди на отправку';
            case self::EMAIL_SENT:
                return 'отправлено заявителю';
            case self::EMAIL_FAILED:
                return 'ошибка отправки';
            default:
                return null;
        }
    }

    /**
     * Форматированная дата создания
     * @return string
     */
    public function getFormattedCreatedAt(): string
    {
        return $this->created_at ? Yii::$app->formatter->asDatetime($this->created_at, 'php:d.m.Y H:i') : '';
    }
}
