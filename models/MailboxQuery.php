<?php

namespace app\models;

use yii\db\ActiveQuery;

/**
 * Запросы к почтовым ящикам.
 *
 * @see Mailbox
 */
class MailboxQuery extends ActiveQuery
{
    /**
     * Только включённые ящики
     * @return $this
     */
    public function active(): self
    {
        return $this->andWhere(['is_active' => true]);
    }

    /**
     * Ящики, с которых можно отправлять ответы
     * @return $this
     */
    public function sendable(): self
    {
        return $this->active()->andWhere(['not', ['smtp_host' => null]])->andWhere(['<>', 'smtp_host', '']);
    }

    /**
     * Загружает справочники, используемые при регистрации заявки
     * @return $this
     */
    public function withRelations(): self
    {
        return $this->with(['defaultOrganization', 'defaultCategory', 'defaultStatus', 'reopenStatus', 'defaultAssigned']);
    }
}
