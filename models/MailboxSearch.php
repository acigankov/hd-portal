<?php

namespace app\models;

use yii\data\ActiveDataProvider;

/**
 * Поиск по почтовым ящикам.
 */
class MailboxSearch extends Mailbox
{
    /** @var string|null Строка поиска по названию и адресу */
    public $search;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['name', 'email', 'imap_host', 'search'], 'safe'],
            [['is_active'], 'boolean'],
        ];
    }

    /**
     * Провайдер данных для списка ящиков
     *
     * @param array $params
     * @param string $formName
     * @return ActiveDataProvider
     */
    public function search($params, $formName = ''): ActiveDataProvider
    {
        $query = Mailbox::find()->withRelations();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
            'sort' => ['defaultOrder' => ['name' => SORT_ASC]],
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            return $dataProvider;
        }

        if (!empty($this->search)) {
            $query->andWhere([
                'or',
                ['like', 'name', $this->search],
                ['like', 'email', $this->search],
                ['like', 'imap_host', $this->search],
            ]);
        }

        $query->andFilterWhere(['is_active' => $this->is_active]);

        return $dataProvider;
    }
}
