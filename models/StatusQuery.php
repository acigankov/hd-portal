<?php

namespace app\models;

use yii\db\ActiveQuery;

/**
 * Query class for Status model
 */
class StatusQuery extends ActiveQuery
{
    /**
     * {@inheritdoc}
     */
    public function active()
    {
        return $this->andWhere(['status' => Status::STATUS_ACTIVE]);
    }

    /**
     * {@inheritdoc}
     */
    public function ordered()
    {
        return $this->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC]);
    }

    /**
     * Фильтр по типу сущности
     * @param string $entityType Тип сущности (requests, tasks, problems, tickets)
     * @return $this
     */
    public function forEntity($entityType)
    {
        $column = 'for_' . $entityType;
        return $this->andWhere([$column => true]);
    }
}
