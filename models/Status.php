<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;

/**
 * Модель статуса
 *
 * @property int $id
 * @property string $name Название статуса
 * @property string $code Код статуса (латиница)
 * @property string|null $description Описание статуса
 * @property string|null $color Цвет статуса (bootstrap badge class)
 * @property bool $is_default Статус по умолчанию
 * @property int $sort_order Порядок сортировки
 * @property bool $for_requests Применяется к заявкам
 * @property bool $for_tasks Применяется к задачам
 * @property bool $for_problems Применяется к проблемам
 * @property bool $for_tickets Применяется к тикетам
 * @property int $status Статус записи (1 - активен, 0 - неактивен)
 * @property string $created_at Дата создания
 * @property string|null $updated_at Дата обновления
 * @property int|null $created_by ID пользователя создателя
 * @property int|null $updated_by ID пользователя редактора
 */
class Status extends ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%statuses}}';
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
                    BaseActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    BaseActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
                'value' => date('Y-m-d H:i:s'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'code'], 'required'],
            [['name', 'code', 'color'], 'string', 'max' => 255],
            [['code'], 'string', 'max' => 50],
            [['code'], 'match', 'pattern' => '/^[a-zA-Z0-9_-]+$/', 'message' => 'Код должен содержать только латинские буквы, цифры, дефис и подчеркивание'],
            [['description'], 'safe'],
            [['is_default', 'for_requests', 'for_tasks', 'for_problems', 'for_tickets'], 'boolean'],
            [['sort_order', 'status', 'created_by', 'updated_by'], 'integer'],
            [['code'], 'unique', 'message' => 'Статус с таким кодом уже существует'],
            [['status'], 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE]],
            [['color'], 'in', 'range' => ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Название статуса',
            'code' => 'Код статуса',
            'description' => 'Описание',
            'color' => 'Цвет',
            'is_default' => 'По умолчанию',
            'sort_order' => 'Порядок сортировки',
            'for_requests' => 'Для заявок',
            'for_tasks' => 'Для задач',
            'for_problems' => 'Для проблем',
            'for_tickets' => 'Для тикетов',
            'entity_types' => 'Применяется для',
            'status' => 'Статус',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата обновления',
            'created_by' => 'Создатель',
            'updated_by' => 'Редактор',
        ];
    }

    /**
     * Возвращает пользователя-создателя
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /**
     * Возвращает пользователя-редактора
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'updated_by']);
    }

    /**
     * Возвращает статус в виде строки
     * @return string
     */
    public function getStatusLabel()
    {
        return $this->status === self::STATUS_ACTIVE ? 'Активен' : 'Неактивен';
    }

    /**
     * Возвращает цвет бейджа
     * @return string
     */
    public function getColorBadgeClass()
    {
        return 'bg-' . ($this->color ?? 'secondary');
    }

    /**
     * Проверяет, применяется ли статус к указанной сущности
     * @param string $entityType Тип сущности (requests, tasks, problems, tickets)
     * @return bool
     */
    public function isForEntity($entityType)
    {
        $property = 'for_' . $entityType;
        return isset($this->$property) && $this->$property;
    }

    /**
     * Возвращает список типов сущностей, для которых применяется статус
     * @return array
     */
    public function getEntityTypes()
    {
        $types = [];
        if ($this->for_requests) {
            $types[] = 'requests';
        }
        if ($this->for_tasks) {
            $types[] = 'tasks';
        }
        if ($this->for_problems) {
            $types[] = 'problems';
        }
        if ($this->for_tickets) {
            $types[] = 'tickets';
        }
        return $types;
    }

    /**
     * {@inheritdoc}
     * @return StatusQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new StatusQuery(get_called_class());
    }
}
