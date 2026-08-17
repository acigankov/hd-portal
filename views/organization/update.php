<?php

use app\models\Organization;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Organization */
/* @var $form yii\widgets\ActiveForm */

$this->title = 'Редактировать организацию: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Организации', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Редактирование';
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
                            <h3 class="card-title">Редактирование организации</h3>



                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <?php $form = ActiveForm::begin(); ?>

                            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
                            <?= $form->field($model, 'inn')->textInput(['maxlength' => 12]) ?>
                            <?= $form->field($model, 'kpp')->textInput(['maxlength' => 9]) ?>
                            <?= $form->field($model, 'ogrn')->textInput(['maxlength' => 15]) ?>
                            <?= $form->field($model, 'director_name')->textInput(['maxlength' => true]) ?>
                            <?= $form->field($model, 'phone')->textInput(['maxlength' => 20]) ?>
                            <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>
                            <?= $form->field($model, 'website')->textInput(['maxlength' => true]) ?>
                            <?= $form->field($model, 'legal_address')->textarea(['rows' => 3]) ?>
                            <?= $form->field($model, 'actual_address')->textarea(['rows' => 3]) ?>
                            <?= $form->field($model, 'status')->dropDownList([
                                Organization::STATUS_ACTIVE => 'Активна',
                                Organization::STATUS_INACTIVE => 'Неактивна',
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
