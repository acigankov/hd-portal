<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Organization $model */
/** @var ActiveForm $form */
?>

<div class="organization-form">

    <?php $form = ActiveForm::begin([
        'options' => [
            'enctype' => 'multipart/form-data'
        ]
    ]); ?>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'class' => 'form-control']) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'inn')->textInput(['maxlength' => 12, 'class' => 'form-control']) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'kpp')->textInput(['maxlength' => 9, 'class' => 'form-control']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'ogrn')->textInput(['maxlength' => 15, 'class' => 'form-control']) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'director_name')->textInput(['maxlength' => true, 'class' => 'form-control']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'phone')->textInput(['maxlength' => 20, 'class' => 'form-control']) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'email')->textInput(['maxlength' => true, 'class' => 'form-control']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <?= $form->field($model, 'website')->textInput(['maxlength' => true, 'class' => 'form-control']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <?= $form->field($model, 'legal_address')->textarea(['rows' => 3, 'class' => 'form-control']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <?= $form->field($model, 'actual_address')->textarea(['rows' => 3, 'class' => 'form-control']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'status')->dropDownList([
                \app\models\Organization::STATUS_ACTIVE => 'Активна',
                \app\models\Organization::STATUS_INACTIVE => 'Неактивна',
            ], ['class' => 'form-select']) ?>
        </div>
    </div>

    <div class="form-group mt-3">
        <?= Html::submitButton($model->isNewRecord ? 'Создать' : 'Сохранить', ['class' => 'btn btn-success']) ?>
        <?= Html::a('Отмена', ['index'], ['class' => 'btn btn-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
