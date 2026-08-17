<?php

namespace app\models;

use Yii;

/**
 * OrganizationSearch model for search forms.
 */
class OrganizationSearch extends Organization
{
    /**
     * @var string Поисковый запрос
     */
    public $search;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'status', 'created_by', 'updated_by'], 'integer'],
            [['name', 'inn', 'kpp', 'ogrn', 'phone', 'email', 'website', 'director_name', 'created_at', 'updated_at', 'search'], 'safe'],
        ];
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @param string $formName
     * @return \yii\data\ActiveDataProvider
     */
    public function search($params, $formName = '')
    {
        $query = Organization::find();

        $dataProvider = new \yii\data\ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'defaultOrder' => [
                    'created_at' => SORT_DESC,
                ],
            ],
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // Поиск по названию или ИНН
        if (!empty($this->search)) {
            $query->andFilterWhere([
                'or',
                ['like', 'name', $this->search],
                ['like', 'inn', $this->search],
                ['like', 'director_name', $this->search],
            ]);
        }

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'inn', $this->inn])
            ->andFilterWhere(['like', 'kpp', $this->kpp])
            ->andFilterWhere(['like', 'ogrn', $this->ogrn])
            ->andFilterWhere(['like', 'phone', $this->phone])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'website', $this->website])
            ->andFilterWhere(['like', 'director_name', $this->director_name])
            ->andFilterWhere(['=', 'status', $this->status]);

        return $dataProvider;
    }
}
