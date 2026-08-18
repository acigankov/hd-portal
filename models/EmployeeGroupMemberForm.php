<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * Модель для формы управления сотрудниками группы
 */
class EmployeeGroupMemberForm extends Model
{
    /**
     * @var int ID группы
     */
    public $groupId;

    /**
     * @var array Выбранные ID сотрудников
     */
    public $employeeIds = [];

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['groupId'], 'required'],
            [['groupId'], 'integer'],
            [['employeeIds'], 'safe'],
            [['employeeIds'], 'each', 'rule' => ['integer']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'groupId' => 'Группа',
            'employeeIds' => 'Сотрудники',
        ];
    }

    /**
     * Загружает текущих сотрудников группы
     * @param int $groupId
     */
    public function loadCurrentMembers($groupId)
    {
        $this->groupId = $groupId;
        $members = EmployeeGroupMember::findAll(['employee_group_id' => $groupId]);
        $this->employeeIds = \yii\helpers\ArrayHelper::getColumn($members, 'user_id');
    }

    /**
     * Сохраняет сотрудников группы
     * @return bool
     */
    public function save()
    {
        if (!$this->validate()) {
            return false;
        }

        // Удаляем старых сотрудников
        EmployeeGroupMember::deleteAll(['employee_group_id' => $this->groupId]);

        // Добавляем новых
        if (!empty($this->employeeIds)) {
            $userId = Yii::$app->user->id;
            $now = date('Y-m-d H:i:s');
            
            foreach ($this->employeeIds as $employeeId) {
                $member = new EmployeeGroupMember();
                $member->employee_group_id = $this->groupId;
                $member->user_id = $employeeId;
                $member->created_at = $now;
                $member->created_by = $userId;
                $member->save(false);
            }
        }

        return true;
    }
}
