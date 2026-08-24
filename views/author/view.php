<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Author */

$this->title = Yii::t('app', 'View author');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Authors'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="author-view">

    <!--begin::App Content Header-->
    <div class="app-content-header">
        <!--begin::Container-->
        <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
                <div class="col-sm-6"><h3 class="mb-0"><?php echo Html::encode($this->title); ?></h3></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="/"><?= Yii::t('app', 'Home') ?></a></li>
                        <li class="breadcrumb-item"><a href="/author"><?= Yii::t('app', 'Authors') ?></a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo Html::encode($this->title); ?></li>
                    </ol>
                </div>
            </div>
            <!--end::Row-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::App Content Header-->
    
    <!--begin::App Content-->
    <div class="app-content">
        <!--begin::Container-->
        <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
                <!--begin::Col-->
                <div class="col">
                    <div class="card">
                        <div class="card-body">
                            <p>
                                <?= Html::a(Yii::t('app', 'Back to list'), ['/author/index'], ['class' => 'btn btn-default']) ?>
                                <?php if (Yii::$app->user->can('admin')): ?>
                                    <?= Html::a(Yii::t('app', 'Edit'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                                    <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
                                        'class' => 'btn btn-danger',
                                        'data' => [
                                            'confirm' => Yii::t('app', 'Confirm deletion of author'),
                                            'method' => 'post',
                                        ],
                                    ]) ?>
                                <?php endif; ?>
                            </p>

                            <?= DetailView::widget([
                                'model' => $model,
                                'attributes' => [
                                    'id',
                                    'full_name',
                                    'email:email',
                                    'phone',
                                    'position',
                                    [
                                        'attribute' => 'organization_id',
                                        'value' => function ($model) {
                                            return $model->organization ? $model->organization->name : null;
                                        },
                                        'label' => Yii::t('app', 'Organization'),
                                    ],
                                    [
                                        'attribute' => 'status',
                                        'value' => function ($model) {
                                            return $model->getStatusLabel();
                                        },
                                        'label' => Yii::t('app', 'Status'),
                                    ],
                                    'created_at',
                                    'updated_at',
                                ],
                            ]) ?>
                        </div>
                    </div>
                </div>
                <!--end::Col-->
            </div>
            <!--end::Row-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::App Content-->

</div>
