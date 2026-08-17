<?php

use yii\helpers\Html;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\OrganizationSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Организации';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="organization-index">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1><?= Html::encode($this->title) ?></h1>
        <?php if (Yii::$app->user->can('admin')): ?>
            <p>
                <?= Html::a('Создать организацию', ['create'], ['class' => 'btn btn-success']) ?>
            </p>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-body">
            <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'options' => [
                    'class' => 'table-responsive'
                ],
                'tableOptions' => [
                    'class' => 'table table-striped table-hover'
                ],
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],

                    [
                        'attribute' => 'name',
                        'label' => 'Название',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return Html::a(Html::encode($model->name), ['view', 'id' => $model->id]);
                        },
                    ],
                    [
                        'attribute' => 'inn',
                        'label' => 'ИНН',
                        'value' => function ($model) {
                            return Html::encode($model->inn);
                        },
                    ],
                    [
                        'attribute' => 'kpp',
                        'label' => 'КПП',
                        'value' => function ($model) {
                            return Html::encode($model->kpp);
                        },
                    ],
                    [
                        'attribute' => 'director_name',
                        'label' => 'Директор',
                        'value' => function ($model) {
                            return Html::encode($model->director_name);
                        },
                    ],
                    [
                        'attribute' => 'phone',
                        'label' => 'Телефон',
                        'value' => function ($model) {
                            return Html::encode($model->phone);
                        },
                    ],
                    [
                        'attribute' => 'email',
                        'label' => 'Email',
                        'format' => 'email',
                        'value' => function ($model) {
                            return Html::encode($model->email);
                        },
                    ],
                    [
                        'attribute' => 'status',
                        'label' => 'Статус',
                        'format' => 'raw',
                        'value' => function ($model) {
                            $badgeClass = $model->status === \app\models\Organization::STATUS_ACTIVE ? 'bg-success' : 'bg-secondary';
                            return '<span class="badge ' . $badgeClass . '">' . Html::encode($model->getStatusLabel()) . '</span>';
                        },
                        'filter' => [
                            \app\models\Organization::STATUS_ACTIVE => 'Активна',
                            \app\models\Organization::STATUS_INACTIVE => 'Неактивна',
                        ],
                    ],

                    [
                        'class' => ActionColumn::class,
                        'header' => 'Действия',
                        'template' => '{view}',
                        'buttons' => [
                            'view' => function ($url, $model) {
                                return Html::a('<i class="bi bi-eye"></i>', $url, [
                                    'class' => 'btn btn-sm btn-outline-primary',
                                    'title' => 'Просмотр',
                                    'data-bs-toggle' => 'tooltip',
                                ]);
                            },
                        ],
                        'visibleButtons' => [
                            'update' => Yii::$app->user->can('admin'),
                            'delete' => Yii::$app->user->can('admin'),
                        ],
                    ],
                ],
            ]) ?>
        </div>
    </div>

</div>

<?php
$js = <<<JS
$(document).ready(function() {
    $('[data-bs-toggle="tooltip"]').tooltip();
});
JS;
$this->registerJs($js);
?>
