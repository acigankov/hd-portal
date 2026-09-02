<?php

namespace app\models;

use app\components\Crypt;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;

/**
 * Почтовый ящик — канал регистрации заявок.
 *
 * Один ящик = один входящий адрес (support@, billing@, sales@) со своими
 * реквизитами доступа и своими правилами оформления заявки. Ответ заявителю
 * всегда уходит с того ящика, на который поступило обращение.
 *
 * Пароли в базе хранятся зашифрованными. Виртуальные атрибуты imap_password и
 * smtp_password используются формой: заполненное значение шифруется при
 * сохранении, пустое оставляет ранее сохранённый пароль без изменений.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property bool $is_active
 * @property string $imap_host
 * @property int $imap_port
 * @property string $imap_encryption
 * @property bool $imap_validate_cert
 * @property string $imap_login
 * @property string|null $imap_password_encrypted
 * @property string $imap_folder
 * @property string|null $smtp_host
 * @property int|null $smtp_port
 * @property string $smtp_encryption
 * @property string|null $smtp_login
 * @property string|null $smtp_password_encrypted
 * @property string|null $from_name
 * @property string|null $reply_to
 * @property string|null $signature
 * @property int|null $default_organization_id
 * @property int|null $default_category_id
 * @property int|null $default_status_id
 * @property int|null $reopen_status_id
 * @property int|null $default_assigned_id
 * @property int $default_priority
 * @property bool $create_authors
 * @property int $last_uid
 * @property string|null $last_checked_at
 * @property string|null $last_error
 * @property string $created_at
 * @property string|null $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 *
 * @property Organization|null $defaultOrganization
 * @property Category|null $defaultCategory
 * @property Status|null $defaultStatus
 * @property Status|null $reopenStatus
 * @property User|null $defaultAssigned
 */
class Mailbox extends ActiveRecord
{
    const ENCRYPTION_SSL = 'ssl';
    const ENCRYPTION_TLS = 'tls';
    const ENCRYPTION_NONE = 'none';

    /** @var string|null Новый пароль IMAP, введённый в форме (не колонка) */
    public $imap_password;

    /** @var string|null Новый пароль SMTP, введённый в форме (не колонка) */
    public $smtp_password;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%mailboxes}}';
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
            [['name', 'email', 'imap_host', 'imap_login'], 'required'],
            [['name', 'email', 'imap_host', 'imap_login', 'imap_folder', 'smtp_host', 'smtp_login', 'from_name', 'reply_to'], 'string', 'max' => 255],
            [['email', 'reply_to'], 'email'],
            [['email'], 'unique', 'message' => 'Ящик с таким адресом уже подключён'],
            [['imap_password', 'smtp_password'], 'string', 'max' => 255],
            [['signature'], 'string', 'max' => 5000],
            [['imap_port', 'smtp_port', 'default_organization_id', 'default_category_id', 'default_status_id', 'reopen_status_id', 'default_assigned_id', 'default_priority', 'last_uid'], 'integer'],
            [['imap_port', 'smtp_port'], 'integer', 'min' => 1, 'max' => 65535],
            [['is_active', 'imap_validate_cert', 'create_authors'], 'boolean'],
            [['imap_encryption', 'smtp_encryption'], 'in', 'range' => array_keys(self::encryptionList())],
            [['imap_folder'], 'default', 'value' => 'INBOX'],
            [['default_priority'], 'default', 'value' => Ticket::PRIORITY_MEDIUM],
            [['default_priority'], 'in', 'range' => array_keys(Ticket::priorityList())],
            [['last_checked_at', 'last_error'], 'safe'],
            [['default_organization_id'], 'exist', 'targetClass' => Organization::class, 'targetAttribute' => 'id', 'skipOnEmpty' => true],
            [['default_category_id'], 'exist', 'targetClass' => Category::class, 'targetAttribute' => 'id', 'skipOnEmpty' => true],
            [['default_status_id', 'reopen_status_id'], 'exist', 'targetClass' => Status::class, 'targetAttribute' => 'id', 'skipOnEmpty' => true],
            [['default_assigned_id'], 'exist', 'targetClass' => User::class, 'targetAttribute' => 'id', 'skipOnEmpty' => true],
            // Отправлять ответы можно только при заполненном SMTP-хосте:
            // без него ящик работает в режиме «только приём писем».
            [['smtp_host'], 'required', 'when' => function (self $model) {
                return !empty($model->smtp_login) || !empty($model->smtp_password);
            }, 'whenClient' => false, 'message' => 'Укажите SMTP-сервер, иначе ответы отправлять некуда'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Название канала',
            'email' => 'Адрес ящика',
            'is_active' => 'Активен',
            'imap_host' => 'IMAP-сервер',
            'imap_port' => 'Порт IMAP',
            'imap_encryption' => 'Шифрование IMAP',
            'imap_validate_cert' => 'Проверять сертификат',
            'imap_login' => 'Логин IMAP',
            'imap_password' => 'Пароль IMAP',
            'imap_folder' => 'Папка',
            'smtp_host' => 'SMTP-сервер',
            'smtp_port' => 'Порт SMTP',
            'smtp_encryption' => 'Шифрование SMTP',
            'smtp_login' => 'Логин SMTP',
            'smtp_password' => 'Пароль SMTP',
            'from_name' => 'Имя отправителя',
            'reply_to' => 'Адрес для ответов',
            'signature' => 'Подпись в письме',
            'default_organization_id' => 'Организация по умолчанию',
            'default_category_id' => 'Категория по умолчанию',
            'default_status_id' => 'Статус новой заявки',
            'reopen_status_id' => 'Статус при ответе заявителя',
            'default_assigned_id' => 'Специалист по умолчанию',
            'default_priority' => 'Приоритет по умолчанию',
            'create_authors' => 'Заводить авторов автоматически',
            'last_uid' => 'Последний UID',
            'last_checked_at' => 'Последняя проверка',
            'last_error' => 'Последняя ошибка',
            'created_at' => 'Создан',
            'updated_at' => 'Изменён',
        ];
    }

    /**
     * {@inheritdoc}
     * @return MailboxQuery
     */
    public static function find()
    {
        return new MailboxQuery(get_called_class());
    }

    /**
     * Варианты шифрования соединения
     * @return array
     */
    public static function encryptionList(): array
    {
        return [
            self::ENCRYPTION_SSL => 'SSL/TLS',
            self::ENCRYPTION_TLS => 'STARTTLS',
            self::ENCRYPTION_NONE => 'Без шифрования',
        ];
    }

    /**
     * Активные ящики
     * @return self[]
     */
    public static function activeList(): array
    {
        return self::find()->active()->orderBy(['name' => SORT_ASC])->all();
    }

    /**
     * Шифруем введённые пароли и не трогаем сохранённые, если поля пустые
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if (!empty($this->imap_password)) {
            $this->imap_password_encrypted = Crypt::encrypt($this->imap_password);
        }

        if (!empty($this->smtp_password)) {
            $this->smtp_password_encrypted = Crypt::encrypt($this->smtp_password);
        }

        if (empty($this->imap_folder)) {
            $this->imap_folder = 'INBOX';
        }

        return true;
    }

    /**
     * Пароль IMAP в открытом виде (только для подключения)
     * @return string|null
     */
    public function getImapPasswordPlain(): ?string
    {
        return Crypt::decrypt($this->imap_password_encrypted);
    }

    /**
     * Пароль SMTP. Если отдельный пароль не задан, используется пароль IMAP —
     * у большинства провайдеров это одна и та же учётная запись.
     * @return string|null
     */
    public function getSmtpPasswordPlain(): ?string
    {
        $password = Crypt::decrypt($this->smtp_password_encrypted);

        return $password !== null ? $password : $this->getImapPasswordPlain();
    }

    /**
     * Логин SMTP с тем же правилом наследования, что и пароль
     * @return string
     */
    public function getSmtpLoginEffective(): string
    {
        return !empty($this->smtp_login) ? (string)$this->smtp_login : (string)$this->imap_login;
    }

    /**
     * Может ли ящик отправлять ответы
     * @return bool
     */
    public function getCanSend(): bool
    {
        return !empty($this->smtp_host) && $this->getSmtpPasswordPlain() !== null;
    }

    /**
     * Строка подключения IMAP вида {host:993/imap/ssl}INBOX
     * @return string
     */
    public function getImapConnectionString(): string
    {
        $flags = '/imap';

        if ($this->imap_encryption === self::ENCRYPTION_SSL) {
            $flags .= '/ssl';
        } elseif ($this->imap_encryption === self::ENCRYPTION_TLS) {
            $flags .= '/tls';
        } else {
            $flags .= '/notls';
        }

        $flags .= $this->imap_validate_cert ? '/validate-cert' : '/novalidate-cert';

        return '{' . $this->imap_host . ':' . (int)$this->imap_port . $flags . '}' . $this->imap_folder;
    }

    /**
     * Отображаемое имя отправителя
     * @return string
     */
    public function getFromNameEffective(): string
    {
        return !empty($this->from_name) ? (string)$this->from_name : (string)$this->name;
    }

    /**
     * Адрес для ответов заявителя
     * @return string
     */
    public function getReplyToEffective(): string
    {
        return !empty($this->reply_to) ? (string)$this->reply_to : (string)$this->email;
    }

    /**
     * Организация по умолчанию
     * @return \yii\db\ActiveQuery
     */
    public function getDefaultOrganization()
    {
        return $this->hasOne(Organization::class, ['id' => 'default_organization_id']);
    }

    /**
     * Категория по умолчанию
     * @return \yii\db\ActiveQuery
     */
    public function getDefaultCategory()
    {
        return $this->hasOne(Category::class, ['id' => 'default_category_id']);
    }

    /**
     * Статус новой заявки
     * @return \yii\db\ActiveQuery
     */
    public function getDefaultStatus()
    {
        return $this->hasOne(Status::class, ['id' => 'default_status_id']);
    }

    /**
     * Статус, в который заявка возвращается после ответа заявителя
     * @return \yii\db\ActiveQuery
     */
    public function getReopenStatus()
    {
        return $this->hasOne(Status::class, ['id' => 'reopen_status_id']);
    }

    /**
     * Специалист по умолчанию
     * @return \yii\db\ActiveQuery
     */
    public function getDefaultAssigned()
    {
        return $this->hasOne(User::class, ['id' => 'default_assigned_id']);
    }

    /**
     * Отмечает результат опроса ящика
     * @param string|null $error
     */
    public function markChecked(?string $error = null): void
    {
        $this->last_checked_at = date('Y-m-d H:i:s');
        $this->last_error = $error;
        $this->save(false, ['last_checked_at', 'last_error', 'last_uid']);
    }
}
