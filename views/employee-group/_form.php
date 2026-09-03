<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\EmployeeGroup $model */
/** @var app\models\Organization[] $organizations */
/** @var app\models\User[] $allEmployees */
/** @var ActiveForm $form */
/** @var array $selectedEmployeeIds */

$selectedIds = isset($selectedEmployeeIds) ? $selectedEmployeeIds : [];
if (!$model->isNewRecord && empty($selectedIds)) {
    $selectedIds = \yii\helpers\ArrayHelper::getColumn($model->employees, 'id');
}

$this->registerJs(<<<JS
    $(document).ready(function() {
        $('#employee-select').select2({
            placeholder: 'Выберите сотрудников для добавления в группу',
            allowClear: true,
            language: 'ru',
            width: '100%',
            dropdownParent: $('.employee-group-form').length ? $('.employee-group-form').closest('.card') : $(document.body)
        });
    });
JS);
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
            <?= $form->field($model, 'description')->textarea(['rows' => 4, 'class' => 'form-control quill-editor', 'placeholder' => 'Введите описание группы']) ?>
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

    <div class="row mt-3">
        <div class="col-md-12">
            <hr class="my-4">
            
            <h4>Сотрудники группы</h4>
            <p class="text-muted mb-3">Выберите сотрудников, которые будут входить в эту группу:</p>
            
            <?php if (!empty($allEmployees)): ?>
                <?= Html::dropDownList(
                    'EmployeeGroup[employee_ids]',
                    $selectedIds,
                    \yii\helpers\ArrayHelper::map($allEmployees, 'id', function($employee) {
                        return $employee->login . ' (' . $employee->email . ')';
                    }),
                    [
                        'id' => 'employee-select',
                        'class' => 'form-control',
                        'multiple' => 'multiple',
                        'prompt' => 'Выберите сотрудников...'
                    ]
                ) ?>
            <?php else: ?>
                <p class="text-muted">Нет доступных сотрудников для добавления.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="form-group mt-3">
        <?= Html::submitButton($model->isNewRecord ? 'Создать' : 'Сохранить', ['class' => 'btn btn-success']) ?>
        <?= Html::a('Отмена', ['index'], ['class' => 'btn btn-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
