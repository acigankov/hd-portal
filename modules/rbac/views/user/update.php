<?php

use app\models\User;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\User */
/* @var $form yii\widgets\ActiveForm */

$this->title = Yii::t('yii2mod.rbac', 'Редактрирование пользователя');
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
                        <li class="breadcrumb-item"><a href="#">Администрирование</a></li>
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
                            <?php $form = ActiveForm::begin(); ?>

                            <?= $form->field($model, 'login')->textInput(['maxlength' => true]) ?>
                            <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>
                            <?= $form->field($model, 'status')->dropDownList([
                                User::STATUS_ACTIVE => 'Активен',
                                User::STATUS_DISABLED => 'Неактивен',
                            ]) ?>

                            <div class="form-group">
                                <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
                                <?= Html::a('Отмена', ['index'], ['class' => 'btn btn-default']) ?>
                            </div>

                            <?php ActiveForm::end(); ?>
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
