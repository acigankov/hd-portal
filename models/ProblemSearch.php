<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ProblemSearch represents the model behind the search form of `app\models\Problem`.
 */
class ProblemSearch extends Problem
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'category_id', 'status_id', 'author_id', 'responsible_id', 'priority', 'status', 'created_by', 'updated_by'], 'integer'],
            [['problem_number', 'title', 'description', 'jira_ticket', 'due_date', 'created_at', 'updated_at'], 'safe'],
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
        $query = Problem::find();

        // add conditions that should always apply here
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere(['like', 'problem_number', $this->problem_number])
            ->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'jira_ticket', $this->jira_ticket])
            ->andFilterWhere(['=', 'category_id', $this->category_id])
            ->andFilterWhere(['=', 'status_id', $this->status_id])
            ->andFilterWhere(['=', 'author_id', $this->author_id])
            ->andFilterWhere(['=', 'responsible_id', $this->responsible_id])
            ->andFilterWhere(['=', 'priority', $this->priority])
            ->andFilterWhere(['=', 'status', $this->status])
            ->andFilterWhere(['=', 'created_by', $this->created_by]);

        if (!empty($this->due_date)) {
            $query->andFilterWhere(['>=', 'due_date', $this->due_date]);
        }

        return $dataProvider;
    }
}
