<?php

use app\models\EmployeeGroup;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\EmployeeGroup */
/* @var $organizations app\models\Organization[] */
/* @var $allEmployees app\models\User[] */
/* @var $form yii\widgets\ActiveForm */
/* @var $selectedEmployeeIds array */

$selectedIds = isset($selectedEmployeeIds) ? $selectedEmployeeIds : [];
if (!$model->isNewRecord && empty($selectedIds)) {
    $selectedIds = \yii\helpers\ArrayHelper::getColumn($model->employees, 'id');
}

$this->title = 'Редактировать группу: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Группы сотрудников', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Редактирование';

$this->registerJs(<<<JS
    $(document).ready(function() {
        $('#employee-select').select2({
            placeholder: 'Выберите сотрудников для добавления в группу',
            allowClear: true,
            language: 'ru',
            width: '100%',
            dropdownParent: $('.employee-group-form').closest('.card')
        });
    });
JS);
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
                            <h3 class="card-title">Редактирование группы сотрудников</h3>


                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <?php $form = ActiveForm::begin(); ?>

                            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
                            <?= $form->field($model, 'description')->textarea(['rows' => 4]) ?>
                            <?= $form->field($model, 'organization_id')->dropDownList(
                                \yii\helpers\ArrayHelper::map($organizations, 'id', 'name'),
                                ['prompt' => 'Выберите организацию']
                            ) ?>
                            <?= $form->field($model, 'status')->dropDownList([
                                EmployeeGroup::STATUS_ACTIVE => 'Активна',
                                EmployeeGroup::STATUS_INACTIVE => 'Неактивна',
                            ]) ?>

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

                            <div class="form-group mt-3">
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
