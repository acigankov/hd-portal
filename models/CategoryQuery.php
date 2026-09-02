<?php

namespace app\models;

use yii\db\ActiveQuery;

/**
 * Query class for Category model
 */
class CategoryQuery extends ActiveQuery
{
    public function active()
    {
        return $this->andWhere(['status' => Category::STATUS_ACTIVE]);
    }

    public function forRequests()
    {
        return $this->andWhere(['for_requests' => true]);
    }

    public function forTasks()
    {
        return $this->andWhere(['for_tasks' => true]);
    }

    public function forProblems()
    {
        return $this->andWhere(['for_problems' => true]);
    }

    public function ordered()
    {
        return $this->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC]);
    }
}
