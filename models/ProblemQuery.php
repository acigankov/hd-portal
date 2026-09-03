<?php

namespace app\models;

/**
 * Query class for Problem model
 */
class ProblemQuery extends \yii\db\ActiveQuery
{
    public function active()
    {
        return $this->andWhere(['status' => Problem::STATUS_ACTIVE]);
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
