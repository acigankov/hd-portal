<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * StatusSearch represents the model behind the search form of `app\models\Status`.
 */
class StatusSearch extends Status
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'sort_order', 'status', 'created_by', 'updated_by'], 'integer'],
            [['name', 'code', 'description', 'color', 'created_at', 'updated_at'], 'safe'],
            [['is_default', 'for_requests', 'for_tasks', 'for_problems', 'for_tickets'], 'boolean'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Status::find();

        // add conditions that should always apply here
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['sort_order' => SORT_ASC, 'id' => SORT_DESC]],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'is_default' => $this->is_default,
            'sort_order' => $this->sort_order,
            'for_requests' => $this->for_requests,
            'for_tasks' => $this->for_tasks,
            'for_problems' => $this->for_problems,
            'for_tickets' => $this->for_tickets,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'code', $this->code])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'color', $this->color]);

        return $dataProvider;
    }
}
