<?php

namespace app\widgets;

use Yii;
use yii\base\Widget;
use yii\data\ActiveDataProvider;
use app\models\Comment;

/**
 * Виджет для отображения блока комментариев.
 * Используется на страницах задач, заявок и проблем.
 */
class CommentsWidget extends Widget
{
    /**
     * @var string|null Имя модели сущности (например, 'app\models\Task')
     */
    public $modelClass;

    /**
     * @var int|null ID сущности (задачи, заявки или проблемы)
     */
    public $modelId;

    /**
     * @var string Заголовок блока
     */
    public $title = 'Комментарии';

    /**
     * @var string Сортировка по умолчанию ('ASC' или 'DESC')
     */
    public $defaultSort = 'DESC';

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        if ($this->modelClass === null || $this->modelId === null) {
            return '<div class="alert alert-danger">Не указаны модель или ID сущности для комментариев.</div>';
        }

        // Проверка существования класса модели
        if (!class_exists($this->modelClass)) {
            return '<div class="alert alert-danger">Класс модели не найден: ' . htmlspecialchars($this->modelClass) . '</div>';
        }

        $dataProvider = new ActiveDataProvider([
            'query' => Comment::find()->where([
                'entity_class' => $this->modelClass,
                'entity_id' => $this->modelId,
            ]),
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes' => ['created_at'],
            ],
        ]);

        // Устанавливаем сортировку по умолчанию из свойства виджета, если не передана в запросе
        if (!Yii::$app->request->get('sort')) {
            $dataProvider->sort->defaultOrder = ['created_at' => $this->defaultSort === 'ASC' ? SORT_ASC : SORT_DESC];
        }

        return $this->render('comments', [
            'dataProvider' => $dataProvider,
            'modelClass' => $this->modelClass,
            'modelId' => $this->modelId,
            'title' => $this->title,
            'defaultSort' => $this->defaultSort,
        ]);
    }
}
