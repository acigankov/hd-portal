<?php

use app\models\Organization;
use yii\helpers\Html;
use yii\web\View;

/* @var $this \yii\web\View */
/* @var $model Organization*/

$this->title = 'Просмотр организации';
$this->params['breadcrumbs'][] = ['label' => 'Организации', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="assignment-index">

    <!--begin::App Content Header-->
    <div class="app-content-header">
        <!--begin::Container-->
        <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
                <div class="col-sm-6"><h3 class="mb-0"><?php echo Html::encode($this->title); ?></h3></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
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
                        <div class="card-header">
                            <h3 class="card-title">Организация (а - ля профиль )</h3>



                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <p>Название: <?= Html::encode($model->name)?> </p>
                            <p>ИНН: <?= Html::encode($model->inn)?> </p>
                            <p>КПП: <?= Html::encode($model->kpp)?> </p>
                            <p>ОГРН: <?= Html::encode($model->ogrn)?> </p>
                            <p>Юридический адрес: <?= Html::encode($model->legal_address)?> </p>
                            <p>Фактический адрес: <?= Html::encode($model->actual_address)?> </p>
                            <p>Телефон: <?= Html::encode($model->phone)?> </p>
                            <p>Email: <?= Html::encode($model->email)?> </p>
                            <p>Сайт: <?= Html::a(Html::encode($model->website), $model->website, ['target' => '_blank'])?> </p>
                            <p>Директор: <?= Html::encode($model->director_name)?> </p>
                            <p>Статус:
                                <?php
                                $badgeClass = $model->status === Organization::STATUS_ACTIVE ? 'bg-success' : 'bg-secondary';
                                ?>
                                <span class="badge <?= $badgeClass ?>"><?= Html::encode($model->getStatusLabel()) ?></span>
                            </p>

                            <hr>

                            <p>Создана: <?= Html::encode($model->formattedCreatedAt) ?> </p>
                            <p>Обновлена: <?= Html::encode($model->formattedUpdatedAt) ?> </p>
                            <?php if ($model->createdBy): ?>
                                <p>Создал: <?= Html::encode($model->createdBy->login)?> </p>
                            <?php endif; ?>
                            <?php if ($model->updatedBy): ?>
                                <p>Обновил: <?= Html::encode($model->updatedBy->login)?> </p>
                            <?php endif; ?>
                        </div>
                        <!-- /.card-body -->
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
