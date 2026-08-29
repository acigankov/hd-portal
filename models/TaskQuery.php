<?php

namespace app\models;

/**
 * Query class for Task model
 */
class TaskQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        return $this->andWhere(['status' => Task::STATUS_ACTIVE]);
    }

    public function orderByPriority()
    {
        return $this->orderBy(['priority' => SORT_DESC]);
    }

    public function orderByDueDate()
    {
        return $this->orderBy(['due_date' => SORT_ASC]);
    }
}
