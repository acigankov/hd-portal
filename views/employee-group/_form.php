<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\EmployeeGroup $model */
/** @var app\models\Organization[] $organizations */
/** @var ActiveForm $form */
?>

<div class="employee-group-form">

    <?php $form = ActiveForm::begin([
        'options' => [
            'enctype' => 'multipart/form-data'
        ]
    ]); ?>

    <div class="row">
        <div class="col-md-12">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'class' => 'form-control', 'placeholder' => 'Введите название группы']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <?= $form->field($model, 'description')->textarea(['rows' => 4, 'class' => 'form-control', 'placeholder' => 'Введите описание группы']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'organization_id')->dropDownList(
                \yii\helpers\ArrayHelper::map($organizations, 'id', 'name'),
                ['prompt' => 'Выберите организацию', 'class' => 'form-select']
            ) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'status')->dropDownList([
                \app\models\EmployeeGroup::STATUS_ACTIVE => 'Активна',
                \app\models\EmployeeGroup::STATUS_INACTIVE => 'Неактивна',
            ], ['class' => 'form-select']) ?>
        </div>
    </div>

    <div class="form-group mt-3">
        <?= Html::submitButton($model->isNewRecord ? 'Создать' : 'Сохранить', ['class' => 'btn btn-success']) ?>
        <?= Html::a('Отмена', ['index'], ['class' => 'btn btn-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
