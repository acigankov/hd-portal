<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Поиск и фильтрация заявок для списка.
 */
class TicketSearch extends Ticket
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'organization_id', 'author_id', 'assigned_id', 'category_id', 'status_id', 'priority'], 'integer'],
            [['ticket_number', 'subject', 'description', 'author_name', 'created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // Сценарии родителя не нужны: модель используется только для фильтра.
        return Model::scenarios();
    }

    /**
     * Строит провайдер данных с учётом фильтров
     *
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Ticket::find()->withRelations();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
                'pageParam' => 'ticket-page',
            ],
            'sort' => [
                'attributes' => [
                    'ticket_number',
                    'subject',
                    'priority',
                    'created_at',
                    'updated_at',
                ],
                'defaultOrder' => ['created_at' => SORT_DESC],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere(['id' => $this->id])
            ->andFilterWhere(['organization_id' => $this->organization_id])
            ->andFilterWhere(['assigned_id' => $this->assigned_id])
            ->andFilterWhere(['category_id' => $this->category_id])
            ->andFilterWhere(['status_id' => $this->status_id])
            ->andFilterWhere(['priority' => $this->priority])
            ->andFilterWhere(['like', 'ticket_number', $this->ticket_number])
            ->andFilterWhere(['like', 'author_name', $this->author_name]);

        // Одно поле поиска ищет и по теме, и по описанию: оператор обычно
        // помнит формулировку обращения, а не то, куда её вписали.
        if (!empty($this->subject)) {
            $query->andWhere([
                'or',
                ['like', 'subject', $this->subject],
                ['like', 'description', $this->subject],
            ]);
        }

        return $dataProvider;
    }
}
