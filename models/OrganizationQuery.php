<?php

namespace app\models;

/**
 * @method Organization[] all()
 * @method Organization|null one()
 */
class OrganizationQuery extends \yii\db\ActiveQuery
{
    /**
     * Возвращает только активные организации
     * @return $this
     */
    public function active()
    {
        return $this->andWhere(['status' => Organization::STATUS_ACTIVE]);
    }

    /**
     * Возвращает только неактивные организации
     * @return $this
     */
    public function inactive()
    {
        return $this->andWhere(['status' => Organization::STATUS_INACTIVE]);
    }

    /**
     * Поиск по названию или ИНН
     * @param string $query
     * @return $this
     */
    public function search($query)
    {
        return $this->andFilterWhere([
            'or',
            ['like', 'name', $query],
            ['like', 'inn', $query],
        ]);
    }
}
