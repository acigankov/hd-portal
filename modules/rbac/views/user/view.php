<?php

use app\models\User;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/* @var $this \yii\web\View */
/* @var $gridViewColumns array */
/* @var $dataProvider \yii\data\ArrayDataProvider */
/* @var $searchModel \app\modules\rbac\models\search\AssignmentSearch */
/* @var $model User*/

$this->title = Yii::t('yii2mod.rbac', 'Просмотр пользоветеля');
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
                            <h3 class="card-title">Пользователь (а - ля профиль )</h3>



                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <p>Логин: <?= Html::encode($model->login) ?> </p>
                            <p>Email: <?= Html::encode($model->email) ?> </p>
                            <p>Статус: <?= $model->status ? 'Активен' : 'Неактивен' ?> </p>
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
