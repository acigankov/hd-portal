<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;

/**
 * Модель группы сотрудников
 *
 * @property int $id
 * @property string $name Название группы
 * @property string|null $description Описание группы
 * @property int|null $organization_id ID организации
 * @property int $status Статус (1 - активна, 0 - неактивна)
 * @property string $created_at Дата создания
 * @property string|null $updated_at Дата обновления
 * @property int|null $created_by ID пользователя создателя
 * @property int|null $updated_by ID пользователя редактора
 * @property User[] $members Сотрудники в группе
 */
class EmployeeGroup extends ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    /**
     * @var array IDs выбранных сотрудников для добавления в группу
     */
    public $employee_ids = [];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%employee_groups}}';
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
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['description'], 'safe'],
            [['organization_id', 'created_by', 'updated_by'], 'integer'],
            [['status'], 'integer'],
            [['status'], 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE]],
            [['employee_ids'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Название группы',
            'description' => 'Описание группы',
            'organization_id' => 'Организация',
            'status' => 'Статус',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата обновления',
            'created_by' => 'Создатель',
            'updated_by' => 'Редактор',
            'employee_ids' => 'Сотрудники',
        ];
    }

    /**
     * Возвращает организацию
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization()
    {
        return $this->hasOne(Organization::class, ['id' => 'organization_id']);
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
     * Возвращает сотрудников в группе
     * @return \yii\db\ActiveQuery
     */
    public function getMembers()
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])
            ->viaTable('{{%employee_group_members}}', ['employee_group_id' => 'id']);
    }

    /**
     * Алиас для getMembers() для совместимости
     * Возвращает сотрудников в группе
     * @return \yii\db\ActiveQuery
     */
    public function getEmployees()
    {
        return $this->getMembers();
    }

    /**
     * Проверяет, есть ли в группе сотрудники
     * @return bool
     */
    public function hasMembers()
    {
        return !empty($this->getMembers()->one());
    }

    /**
     * Возвращает статус в виде строки
     * @return string
     */
    public function getStatusLabel()
    {
        return $this->status === self::STATUS_ACTIVE ? 'Активна' : 'Неактивна';
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
}
