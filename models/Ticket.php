<?php

namespace app\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;
use yii\db\IntegrityException;

/**
 * Модель заявки (тикета)
 *
 * @property int $id
 * @property string $ticket_number Номер заявки в формате tkt#000001
 * @property string $subject Тема обращения
 * @property string|null $description Описание обращения
 * @property int|null $organization_id ID организации
 * @property int|null $mailbox_id Почтовый ящик, через который поступило обращение
 * @property int|null $author_id ID заявителя в справочнике авторов
 * @property string $author_name ФИО заявителя (слепок на момент обращения)
 * @property string|null $author_email Email заявителя
 * @property string|null $author_phone Телефон заявителя
 * @property int|null $assigned_id ID назначенного специалиста
 * @property int|null $category_id ID категории
 * @property int|null $status_id ID статуса
 * @property int $priority Приоритет
 * @property string $created_at Дата создания
 * @property string|null $updated_at Дата обновления
 * @property int|null $created_by ID пользователя создателя
 * @property int|null $updated_by ID пользователя редактора
 *
 * @property Organization|null $organization
 * @property Mailbox|null $mailbox
 * @property Author|null $author
 * @property User|null $assigned
 * @property Category|null $category
 * @property Status|null $ticketStatus
 * @property User|null $createdBy
 * @property User|null $updatedBy
 * @property TicketReply[] $replies
 * @property string $priorityLabel
 * @property string $priorityBadgeClass
 * @property string $authorDisplayName
 */
class Ticket extends ActiveRecord
{
    const PRIORITY_LOW = 1;
    const PRIORITY_MEDIUM = 2;
    const PRIORITY_HIGH = 3;
    const PRIORITY_CRITICAL = 4;

    /** Префикс номера заявки */
    const NUMBER_PREFIX = 'tkt#';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%tickets}}';
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
            [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['subject', 'author_name'], 'required'],
            [['subject', 'author_name', 'author_email'], 'string', 'max' => 255],
            [['author_phone'], 'string', 'max' => 50],
            [['description'], 'string'],
            [['organization_id', 'mailbox_id', 'author_id', 'assigned_id', 'category_id', 'status_id', 'priority'], 'integer'],
            [['author_email'], 'email'],
            [['priority'], 'default', 'value' => self::PRIORITY_MEDIUM],
            [['priority'], 'in', 'range' => array_keys(self::priorityList())],
            [['ticket_number'], 'string', 'max' => 20],
            [['ticket_number'], 'unique', 'message' => 'Заявка с таким номером уже существует'],
            // Ссылочная целостность проверяется до обращения к БД, чтобы
            // пользователь видел понятную ошибку вместо ошибки внешнего ключа.
            [['organization_id'], 'exist', 'targetClass' => Organization::class, 'targetAttribute' => 'id', 'skipOnEmpty' => true],
            [['mailbox_id'], 'exist', 'targetClass' => Mailbox::class, 'targetAttribute' => 'id', 'skipOnEmpty' => true],
            [['author_id'], 'exist', 'targetClass' => Author::class, 'targetAttribute' => 'id', 'skipOnEmpty' => true],
            [['assigned_id'], 'exist', 'targetClass' => User::class, 'targetAttribute' => 'id', 'skipOnEmpty' => true],
            [['category_id'], 'exist', 'targetClass' => Category::class, 'targetAttribute' => 'id', 'skipOnEmpty' => true],
            [['status_id'], 'exist', 'targetClass' => Status::class, 'targetAttribute' => 'id', 'skipOnEmpty' => true],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ticket_number' => 'Номер',
            'subject' => 'Тема',
            'description' => 'Описание',
            'organization_id' => 'Организация',
            'mailbox_id' => 'Почтовый канал',
            'author_id' => 'Заявитель из справочника',
            'author_name' => 'Автор обращения',
            'author_email' => 'Email автора',
            'author_phone' => 'Телефон автора',
            'assigned_id' => 'Назначенный специалист',
            'category_id' => 'Категория',
            'status_id' => 'Статус',
            'priority' => 'Приоритет',
            'created_at' => 'Создана',
            'updated_at' => 'Изменена',
            'created_by' => 'Создал',
            'updated_by' => 'Изменил',
        ];
    }

    /**
     * {@inheritdoc}
     * @return TicketQuery
     */
    public static function find()
    {
        return new TicketQuery(get_called_class());
    }

    /**
     * Список приоритетов
     * @return array
     */
    public static function priorityList(): array
    {
        return [
            self::PRIORITY_LOW => 'Низкий',
            self::PRIORITY_MEDIUM => 'Средний',
            self::PRIORITY_HIGH => 'Высокий',
            self::PRIORITY_CRITICAL => 'Критичный',
        ];
    }

    /**
     * Статусы, доступные заявкам.
     *
     * Берутся из справочника по флагу «для тикетов». Если ни один статус
     * не помечен (свежая база, справочник ещё не заполнен), возвращаются все
     * активные — иначе раздел нельзя было бы использовать вообще.
     *
     * @return Status[]
     */
    public static function statusList(): array
    {
        $flagged = Status::find()
            ->andWhere(['status' => 1])
            ->andWhere(['for_tickets' => true])
            ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])
            ->all();

        if (!empty($flagged)) {
            return $flagged;
        }

        return Status::find()
            ->andWhere(['status' => 1])
            ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])
            ->all();
    }

    /**
     * Категории, доступные заявкам. Логика та же, что у статусов.
     *
     * @return Category[]
     */
    public static function categoryList(): array
    {
        $flagged = Category::find()
            ->andWhere(['status' => 1])
            ->andWhere(['for_tickets' => true])
            ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])
            ->all();

        if (!empty($flagged)) {
            return $flagged;
        }

        return Category::find()
            ->andWhere(['status' => 1])
            ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])
            ->all();
    }

    /**
     * Название приоритета
     * @return string
     */
    public function getPriorityLabel(): string
    {
        $list = self::priorityList();

        return $list[$this->priority] ?? '—';
    }

    /**
     * Класс бейджа для приоритета
     * @return string
     */
    public function getPriorityBadgeClass(): string
    {
        switch ((int)$this->priority) {
            case self::PRIORITY_LOW:
                return 'text-bg-secondary';
            case self::PRIORITY_HIGH:
                return 'text-bg-warning';
            case self::PRIORITY_CRITICAL:
                return 'text-bg-danger';
            default:
                return 'text-bg-info';
        }
    }

    /**
     * Подпись автора обращения: слепок в заявке, иначе запись справочника
     * @return string
     */
    public function getAuthorDisplayName(): string
    {
        if (!empty($this->author_name)) {
            return $this->author_name;
        }

        return $this->author ? (string)$this->author->full_name : '—';
    }

    /**
     * Организация
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization()
    {
        return $this->hasOne(Organization::class, ['id' => 'organization_id']);
    }

    /**
     * Почтовый ящик, с которого пришло обращение и на который уйдут ответы
     * @return \yii\db\ActiveQuery
     */
    public function getMailbox()
    {
        return $this->hasOne(Mailbox::class, ['id' => 'mailbox_id']);
    }

    /**
     * Можно ли ответить заявителю по email.
     *
     * Нужен и почтовый канал с настроенным SMTP, и адрес заявителя:
     * заявка, созданная оператором с телефона, письма не порождает.
     *
     * @return bool
     */
    public function getCanEmailAuthor(): bool
    {
        return !empty($this->author_email)
            && $this->mailbox !== null
            && $this->mailbox->is_active
            && $this->mailbox->getCanSend();
    }

    /**
     * Заявитель из справочника авторов
     * @return \yii\db\ActiveQuery
     */
    public function getAuthor()
    {
        return $this->hasOne(Author::class, ['id' => 'author_id']);
    }

    /**
     * Назначенный специалист
     * @return \yii\db\ActiveQuery
     */
    public function getAssigned()
    {
        return $this->hasOne(User::class, ['id' => 'assigned_id']);
    }

    /**
     * Категория
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    /**
     * Статус из справочника.
     *
     * Связь названа ticketStatus, а не status: в таблице есть колонка status_id,
     * и связь с именем status конфликтовала бы с колонкой при обращении
     * к $model->status.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTicketStatus()
    {
        return $this->hasOne(Status::class, ['id' => 'status_id']);
    }

    /**
     * Создатель заявки
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /**
     * Последний редактор заявки
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'updated_by']);
    }

    /**
     * Обсуждение по заявке
     * @return \yii\db\ActiveQuery
     */
    public function getReplies()
    {
        return $this->hasMany(TicketReply::class, ['ticket_id' => 'id'])
            ->orderBy(['created_at' => SORT_ASC, 'id' => SORT_ASC]);
    }

    /**
     * Перед сохранением подставляем номер, статус по умолчанию и контакты автора
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($insert && empty($this->ticket_number)) {
            $this->ticket_number = self::nextNumber();
        }

        if ($insert && empty($this->status_id)) {
            $statuses = self::statusList();
            foreach ($statuses as $status) {
                if ($status->is_default) {
                    $this->status_id = $status->id;
                    break;
                }
            }
            if (empty($this->status_id) && !empty($statuses)) {
                $this->status_id = reset($statuses)->id;
            }
        }

        $this->fillAuthorSnapshot();

        return true;
    }

    /**
     * Дополняет слепок заявителя данными из справочника, если оператор выбрал
     * запись и не стал переписывать контакты руками.
     */
    protected function fillAuthorSnapshot(): void
    {
        if (empty($this->author_id)) {
            return;
        }

        $author = $this->author;
        if ($author === null) {
            return;
        }

        if (empty($this->author_name)) {
            $this->author_name = (string)$author->full_name;
        }
        if (empty($this->author_email)) {
            $this->author_email = $author->email;
        }
        if (empty($this->author_phone)) {
            $this->author_phone = $author->phone;
        }
        if (empty($this->organization_id)) {
            $this->organization_id = $author->organization_id;
        }
    }

    /**
     * Сохраняет заявку, переgenerируя номер при коллизии.
     *
     * Номер считается как максимум существующих, поэтому два оператора,
     * сохранившие заявку одновременно, могут получить одно значение. Уникальный
     * индекс в этом случае отклонит вставку, и попытка повторяется с новым
     * номером — вместо ошибки на экране.
     *
     * @param int $attempts
     * @return bool
     * @throws IntegrityException
     */
    public function saveWithNumber(int $attempts = 3): bool
    {
        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            try {
                return $this->save();
            } catch (IntegrityException $exception) {
                $isLastAttempt = $attempt === $attempts;
                $isNumberCollision = stripos($exception->getMessage(), 'ticket_number') !== false;

                if ($isLastAttempt || !$isNumberCollision) {
                    throw $exception;
                }

                $this->ticket_number = null;
            }
        }

        return false;
    }

    /**
     * Следующий номер заявки в формате tkt#000001
     * @return string
     */
    public static function nextNumber(): string
    {
        $last = self::find()
            ->select('ticket_number')
            ->orderBy(['id' => SORT_DESC])
            ->limit(1)
            ->scalar();

        $number = 0;
        if (!empty($last)) {
            $number = (int)substr((string)$last, strlen(self::NUMBER_PREFIX));
        }

        return self::NUMBER_PREFIX . str_pad((string)($number + 1), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Меняет статус и пишет системную запись в обсуждение
     *
     * @param int $statusId
     * @return bool
     */
    public function changeStatus(int $statusId): bool
    {
        $previousStatusId = $this->status_id !== null ? (int)$this->status_id : null;

        if ($previousStatusId === $statusId) {
            return true;
        }

        $this->status_id = $statusId;

        if (!$this->save(true, ['status_id', 'updated_at', 'updated_by'])) {
            return false;
        }

        $entry = new TicketReply();
        $entry->ticket_id = $this->id;
        $entry->type = TicketReply::TYPE_SYSTEM;
        $entry->author_side = TicketReply::SIDE_OPERATOR;
        $entry->user_id = Yii::$app->user->isGuest ? null : (int)Yii::$app->user->id;
        $entry->author_name = Yii::$app->user->isGuest ? null : TicketReply::currentUserName();
        $entry->status_from_id = $previousStatusId;
        $entry->status_to_id = $statusId;

        if (!$entry->save()) {
            Yii::error('Не удалось записать смену статуса заявки: ' . print_r($entry->errors, true), __METHOD__);
        }

        return true;
    }

    /**
     * Форматированная дата создания
     * @return string
     */
    public function getFormattedCreatedAt(): string
    {
        return $this->created_at ? Yii::$app->formatter->asDatetime($this->created_at, 'php:d.m.Y H:i') : '';
    }

    /**
     * Форматированная дата изменения
     * @return string
     */
    public function getFormattedUpdatedAt(): string
    {
        return $this->updated_at ? Yii::$app->formatter->asDatetime($this->updated_at, 'php:d.m.Y H:i') : '';
    }
}
