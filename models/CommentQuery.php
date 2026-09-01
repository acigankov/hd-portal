<?php

namespace app\models;

/**
 * CommentQuery - активный запрос для модели Comment
 */
class CommentQuery extends \yii\db\ActiveQuery
{
    /**
     * Комментарии для конкретной сущности
     * @param string $entityType тип сущности (task, ticket, issue)
     * @param int $entityId ID сущности
     * @return $this
     */
    public function forEntity($entityType, $entityId)
    {
        return $this->andWhere(['entity_type' => $entityType, 'entity_id' => $entityId]);
    }

    /**
     * Только корневые комментарии (без родителя)
     * @return $this
     */
    public function root()
    {
        return $this->andWhere(['parent_id' => null]);
    }

    /**
     * Сортировка по дате создания
     * @param string $direction SORT_ASC или SORT_DESC
     * @return $this
     */
    public function orderByDate($direction = SORT_ASC)
    {
        return $this->orderBy(['created_at' => $direction]);
    }

    /**
     * С ответами
     * @return $this
     */
    public function withReplies()
    {
        return $this->with('replies');
    }
}
