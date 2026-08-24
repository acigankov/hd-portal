<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;

/**
 * Модель категории
 *
 * @property int $id
 * @property string $name Название категории
 * @property string $code Код категории (латиница)
 * @property string|null $description Описание категории
 * @property string|null $color Цвет категории (bootstrap badge class)
 * @property string|null $icon Иконка категории (bootstrap icon)
 * @property int $sort_order Порядок сортировки
 * @property bool $for_requests Применяется к заявкам
 * @property bool $for_tasks Применяется к задачам
 * @property bool $for_problems Применяется к проблемам
 * @property int $status Статус записи (1 - активен, 0 - неактивен)
 * @property string $created_at Дата создания
 * @property string|null $updated_at Дата обновления
 * @property int|null $created_by ID пользователя создателя
 * @property int|null $updated_by ID пользователя редактора
 */
class Category extends ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%categories}}';
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
            [['name', 'code', 'color', 'icon'], 'string', 'max' => 255],
            [['code'], 'string', 'max' => 50],
            [['code'], 'match', 'pattern' => '/^[a-zA-Z0-9_-]+$/', 'message' => 'Код должен содержать только латинские буквы, цифры, дефис и подчеркивание'],
            [['description'], 'safe'],
            [['for_requests', 'for_tasks', 'for_problems'], 'boolean'],
            [['sort_order', 'status', 'created_by', 'updated_by'], 'integer'],
            [['code'], 'unique', 'message' => 'Категория с таким кодом уже существует'],
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
            'name' => Yii::t('app', 'Name'),
            'code' => Yii::t('app', 'Code'),
            'description' => Yii::t('app', 'Description'),
            'color' => Yii::t('app', 'Color'),
            'icon' => Yii::t('app', 'Icon'),
            'sort_order' => Yii::t('app', 'Sort Order'),
            'for_requests' => Yii::t('app', 'For Requests'),
            'for_tasks' => Yii::t('app', 'For Tasks'),
            'for_problems' => Yii::t('app', 'For Problems'),
            'entity_types' => Yii::t('app', 'Entity Types'),
            'status' => Yii::t('app', 'Status'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_by' => Yii::t('app', 'Updated By'),
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
        return $this->status === self::STATUS_ACTIVE ? Yii::t('app', 'Active') : Yii::t('app', 'Inactive');
    }

    /**
     * Возвращает цвет бейджа
     * @return string
     */
    public function getColorBadgeClass()
    {
        return 'bg-' . ($this->color ?? 'primary');
    }

    /**
     * Возвращает иконку с префиксом bootstrap
     * @return string
     */
    public function getIconClass()
    {
        return $this->icon ? 'bi-' . $this->icon : 'bi-folder';
    }

    /**
     * Проверяется ли категория к указанной сущности
     * @param string $entityType Тип сущности (requests, tasks, problems)
     * @return bool
     */
    public function isForEntity($entityType)
    {
        $property = 'for_' . $entityType;
        return isset($this->$property) && $this->$property;
    }

    /**
     * Возвращает список типов сущностей, для которых применяется категория
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
        return $types;
    }

    /**
     * Возвращает дату создания в формате d.m.Y H:i
     * @return string
     */
    public function getFormattedCreatedAt()
    {
        return $this->created_at ? date('d.m.Y H:i', strtotime($this->created_at)) : '';
    }

    /**
     * Возвращает дату обновления в формате d.m.Y H:i
     * @return string
     */
    public function getFormattedUpdatedAt()
    {
        return $this->updated_at ? date('d.m.Y H:i', strtotime($this->updated_at)) : '';
    }

    /**
     * {@inheritdoc}
     * @return CategoryQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CategoryQuery(get_called_class());
    }
}
