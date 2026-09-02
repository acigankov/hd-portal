<?php

namespace app\models;

/**
 * Query class for Ticket model
 */
class TicketQuery extends \yii\db\ActiveQuery
{
    /**
     * Заявки конкретной организации
     * @param int $organizationId
     * @return $this
     */
    public function byOrganization(int $organizationId)
    {
        return $this->andWhere(['organization_id' => $organizationId]);
    }

    /**
     * Заявки, назначенные на специалиста
     * @param int $userId
     * @return $this
     */
    public function assignedTo(int $userId)
    {
        return $this->andWhere(['assigned_id' => $userId]);
    }

    /**
     * Сначала самые важные, внутри — самые новые
     * @return $this
     */
    public function orderByPriority()
    {
        return $this->orderBy(['priority' => SORT_DESC, 'created_at' => SORT_DESC]);
    }

    /**
     * Подгружает справочники, чтобы список не делал запрос на каждую строку
     * @return $this
     */
    public function withRelations()
    {
        return $this->with(['organization', 'author', 'assigned', 'category', 'ticketStatus']);
    }
}
