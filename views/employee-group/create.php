<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\EmployeeGroup */
/* @var $organizations app\models\Organization[] */
/* @var $allEmployees app\models\User[] */
/* @var $form yii\widgets\ActiveForm */

$this->title = 'Создать группу сотрудников';
$this->params['breadcrumbs'][] = ['label' => 'Группы сотрудников', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

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


<!--begin::App Content -->
<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="employee-group-form">
                    <div class="card">
                        <div class="card-body">
                            <?php $form = ActiveForm::begin([
                                'id' => 'employee-group-create-form',
                                'errorCssClass' => 'invalid-feedback',
                                'fieldConfig' => [
                                    'template' => "{label}\n<div class=\"col\">{input}</div>\n<div class=\"col\">{error}</div>",
                                    'labelOptions' => ['class' => 'col-sm-2 col-form-label'],
                                ],
                            ]); ?>

                            <?= $form->field($model, 'name')
                                ->textInput(['autofocus' => true, 'placeholder' => 'Введите название группы'])
                                ->label('Название группы *') ?>

                            <?= $form->field($model, 'description')
                                ->textarea(['rows' => 4, 'placeholder' => 'Введите описание группы'])
                                ->label('Описание') ?>

                            <?= $form->field($model, 'organization_id')
                                ->dropDownList(
                                    \yii\helpers\ArrayHelper::map($organizations, 'id', 'name'),
                                    ['prompt' => 'Выберите организацию']
                                )
                                ->label('Организация') ?>

                            <?= $form->field($model, 'status')
                                ->dropDownList([
                                    \app\models\EmployeeGroup::STATUS_ACTIVE => 'Активна',
                                    \app\models\EmployeeGroup::STATUS_INACTIVE => 'Неактивна',
                                ])
                                ->label('Статус') ?>

                            <hr class="my-4">
                            
                            <h4>Сотрудники группы</h4>
                            <p class="text-muted mb-3">Выберите сотрудников, которые будут входить в эту группу:</p>
                            
                            <?php if (!empty($allEmployees)): ?>
                                <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                                    <?php foreach ($allEmployees as $employee): ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" 
                                                   name="EmployeeGroup[employee_ids][]" 
                                                   value="<?= $employee->id ?>" 
                                                   id="employee_<?= $employee->id ?>">
                                            <label class="form-check-label" for="employee_<?= $employee->id ?>">
                                                <?= Html::encode($employee->login) ?> (<?= Html::encode($employee->email) ?>)
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">Нет доступных сотрудников для добавления.</p>
                            <?php endif; ?>

                            <div class="form-group mt-3">
                                <?= Html::submitButton('<i class="fas fa-save"></i> Создать группу', [
                                    'class' => 'btn btn-primary'
                                ]) ?>
                                <?= Html::a('Отмена', ['index'], ['class' => 'btn btn-secondary']) ?>
                            </div>

                            <?php ActiveForm::end(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!--end::App Content -->
