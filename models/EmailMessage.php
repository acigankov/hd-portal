<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;

/**
 * Запись журнала писем.
 *
 * Входящие письма пишутся здесь до создания заявки — по уникальному индексу
 * (mailbox_id, message_id) повторная обработка того же письма отбрасывается.
 * Исходящие письма служат очередью: запись со статусом queued отправляется
 * консольной командой mail/send-pending и, при ошибке SMTP, остаётся в очереди.
 *
 * @property int $id
 * @property int $mailbox_id
 * @property int|null $ticket_id
 * @property int|null $reply_id
 * @property string $direction
 * @property string $status
 * @property string|null $message_id
 * @property string|null $in_reply_to
 * @property string|null $reference_ids
 * @property int|null $imap_uid
 * @property string|null $from_email
 * @property string|null $from_name
 * @property string|null $to_email
 * @property string|null $subject
 * @property string|null $body_text
 * @property string|null $body_html
 * @property string|null $raw_headers
 * @property int $attempts
 * @property string|null $error_message
 * @property string|null $received_at
 * @property string|null $sent_at
 * @property string $created_at
 * @property string|null $updated_at
 *
 * @property Mailbox $mailbox
 * @property Ticket|null $ticket
 * @property TicketReply|null $reply
 * @property EmailAttachment[] $attachments
 */
class EmailMessage extends ActiveRecord
{
    const DIRECTION_INCOMING = 'incoming';
    const DIRECTION_OUTGOING = 'outgoing';

    const STATUS_RECEIVED = 'received';
    const STATUS_PROCESSED = 'processed';
    const STATUS_SKIPPED = 'skipped';
    const STATUS_QUEUED = 'queued';
    const STATUS_SENT = 'sent';
    const STATUS_FAILED = 'failed';

    /** Сколько раз пробуем отправить письмо, прежде чем признать отправку неудачной */
    const MAX_ATTEMPTS = 5;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%email_messages}}';
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
            [['mailbox_id', 'direction', 'status'], 'required'],
            [['mailbox_id', 'ticket_id', 'reply_id', 'imap_uid', 'attempts'], 'integer'],
            [['direction'], 'in', 'range' => [self::DIRECTION_INCOMING, self::DIRECTION_OUTGOING]],
            [['status'], 'in', 'range' => [
                self::STATUS_RECEIVED,
                self::STATUS_PROCESSED,
                self::STATUS_SKIPPED,
                self::STATUS_QUEUED,
                self::STATUS_SENT,
                self::STATUS_FAILED,
            ]],
            [['message_id', 'in_reply_to', 'from_email', 'from_name', 'to_email'], 'string', 'max' => 255],
            [['subject'], 'string', 'max' => 500],
            [['reference_ids', 'body_text', 'body_html', 'raw_headers', 'error_message'], 'string'],
            [['received_at', 'sent_at'], 'safe'],
            [['mailbox_id'], 'exist', 'targetClass' => Mailbox::class, 'targetAttribute' => 'id'],
            [['ticket_id'], 'exist', 'targetClass' => Ticket::class, 'targetAttribute' => 'id', 'skipOnEmpty' => true],
            [['reply_id'], 'exist', 'targetClass' => TicketReply::class, 'targetAttribute' => 'id', 'skipOnEmpty' => true],
        ];
    }

    /**
     * Почтовый ящик
     * @return \yii\db\ActiveQuery
     */
    public function getMailbox()
    {
        return $this->hasOne(Mailbox::class, ['id' => 'mailbox_id']);
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
     * Запись обсуждения
     * @return \yii\db\ActiveQuery
     */
    public function getReply()
    {
        return $this->hasOne(TicketReply::class, ['id' => 'reply_id']);
    }

    /**
     * Файлы, пришедшие с письмом
     * @return \yii\db\ActiveQuery
     */
    public function getAttachments()
    {
        return $this->hasMany(EmailAttachment::class, ['email_message_id' => 'id'])
            ->orderBy(['id' => SORT_ASC]);
    }

    /**
     * Уже обработанное письмо с таким Message-ID.
     *
     * Проверка идёт по конкретному ящику: одно и то же письмо, доставленное
     * и в support@, и в billing@, — это два разных обращения.
     *
     * @param int $mailboxId
     * @param string|null $messageId
     * @return bool
     */
    public static function isDuplicate(int $mailboxId, ?string $messageId): bool
    {
        if (empty($messageId)) {
            return false;
        }

        return self::find()
            ->andWhere(['mailbox_id' => $mailboxId, 'message_id' => $messageId])
            ->exists();
    }

    /**
     * Ищет заявку по идентификаторам писем из заголовков In-Reply-To и References
     *
     * @param string[] $messageIds
     * @return int|null ID заявки
     */
    public static function findTicketIdByMessageIds(array $messageIds): ?int
    {
        $messageIds = array_values(array_filter(array_unique($messageIds), 'strlen'));

        if (empty($messageIds)) {
            return null;
        }

        $ticketId = self::find()
            ->select('ticket_id')
            ->andWhere(['message_id' => $messageIds])
            ->andWhere(['not', ['ticket_id' => null]])
            ->orderBy(['id' => SORT_DESC])
            ->limit(1)
            ->scalar();

        return $ticketId ? (int)$ticketId : null;
    }

    /**
     * Последнее письмо по заявке в заданном направлении.
     *
     * Нужно, чтобы собрать корректные заголовки цепочки в ответе: клиентские
     * почтовые программы группируют переписку по In-Reply-To / References.
     *
     * @param int $ticketId
     * @param string|null $direction
     * @return self|null
     */
    public static function lastForTicket(int $ticketId, ?string $direction = null): ?self
    {
        $query = self::find()->andWhere(['ticket_id' => $ticketId]);

        if ($direction !== null) {
            $query->andWhere(['direction' => $direction]);
        }

        return $query->orderBy(['id' => SORT_DESC])->limit(1)->one();
    }

    /**
     * Отмечает попытку отправки как неудачную
     * @param string $error
     */
    public function markFailed(string $error): void
    {
        $this->attempts = (int)$this->attempts + 1;
        $this->error_message = mb_substr($error, 0, 2000);
        // Пока попытки не исчерпаны, письмо остаётся в очереди: временная
        // недоступность SMTP не должна терять ответ заявителю.
        $this->status = $this->attempts >= self::MAX_ATTEMPTS ? self::STATUS_FAILED : self::STATUS_QUEUED;

        if (!$this->save(false)) {
            Yii::error('Не удалось сохранить состояние письма #' . $this->id, __METHOD__);
        }
    }

    /**
     * Отмечает письмо отправленным
     */
    public function markSent(): void
    {
        $this->attempts = (int)$this->attempts + 1;
        $this->status = self::STATUS_SENT;
        $this->sent_at = date('Y-m-d H:i:s');
        $this->error_message = null;
        $this->save(false);
    }
}
