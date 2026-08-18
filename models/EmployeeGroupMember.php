<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;

/**
 * Модель связи сотрудник-группа
 *
 * @property int $id
 * @property int $employee_group_id ID группы сотрудников
 * @property int $user_id ID сотрудника (пользователя)
 * @property string $created_at Дата добавления
 * @property int|null $created_by ID пользователя, добавившего сотрудника
 */
class EmployeeGroupMember extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%employee_group_members}}';
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
            [['employee_group_id', 'user_id'], 'required'],
            [['employee_group_id', 'user_id', 'created_by'], 'integer'],
            [['employee_group_id', 'user_id'], 'unique', 'targetAttribute' => ['employee_group_id', 'user_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'employee_group_id' => 'Группа',
            'user_id' => 'Сотрудник',
            'created_at' => 'Дата добавления',
            'created_by' => 'Добавил',
        ];
    }

    /**
     * Возвращает группу
     * @return \yii\db\ActiveQuery
     */
    public function getGroup()
    {
        return $this->hasOne(EmployeeGroup::class, ['id' => 'employee_group_id']);
    }

    /**
     * Возвращает сотрудника
     * @return \yii\db\ActiveQuery
     */
    public function getEmployee()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Возвращает пользователя, добавившего запись
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }
}
