<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Organization $model */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Организации', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="organization-view">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1><?= Html::encode($this->title) ?></h1>
        <div>
            <?php if (Yii::$app->user->can('admin')): ?>
                <?= Html::a('Редактировать', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-danger',
                    'data' => [
                        'confirm' => 'Вы уверены, что хотите удалить эту организацию?',
                        'method' => 'post',
                    ],
                ]) ?>
            <?php endif; ?>
            <?= Html::a('Назад к списку', ['index'], ['class' => 'btn btn-secondary']) ?>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <span class="badge <?= $model->status === \app\models\Organization::STATUS_ACTIVE ? 'bg-success' : 'bg-secondary' ?>">
                        <?= $model->getStatusLabel() ?>
                    </span>
                </div>
            </div>

            <?= DetailView::widget([
                'model' => $model,
                'options' => [
                    'class' => 'table table-striped table-bordered detail-table'
                ],
                'attributes' => [
                    'id',
                    'name',
                    'inn',
                    'kpp',
                    'ogrn',
                    [
                        'attribute' => 'legal_address',
                        'format' => 'ntext',
                    ],
                    [
                        'attribute' => 'actual_address',
                        'format' => 'ntext',
                    ],
                    'phone',
                    'email:email',
                    [
                        'attribute' => 'website',
                        'format' => 'url',
                    ],
                    'director_name',
                    [
                        'attribute' => 'created_at',
                        'format' => 'datetime',
                    ],
                    [
                        'attribute' => 'updated_at',
                        'format' => 'datetime',
                    ],
                    [
                        'attribute' => 'created_by',
                        'value' => function ($model) {
                            return $model->createdBy ? $model->createdBy->login : null;
                        },
                    ],
                    [
                        'attribute' => 'updated_by',
                        'value' => function ($model) {
                            return $model->updatedBy ? $model->updatedBy->login : null;
                        },
                    ],
                ],
            ]) ?>
        </div>
    </div>

</div>
