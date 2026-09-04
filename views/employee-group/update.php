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
        // Инициализация Select2 в модальном окне при его открытии
        $('#editMembersModal').on('shown.bs.modal', function () {
            if (!$('#modal-employee-select').data('select2')) {
                $('#modal-employee-select').select2({
                    placeholder: 'Выберите сотрудников...',
                    allowClear: true,
                    language: 'ru',
                    width: '100%',
                    dropdownParent: $('#editMembersModal')
                });
            }
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
                            <?= $form->field($model, 'description')->textarea(['rows' => 4, 'class' => 'form-control quill-editor']) ?>
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
                            <p class="text-muted mb-3">Текущий состав группы:</p>
                            
                            <?php if (!empty($model->members)): ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th style="width: 50px;">#</th>
                                                <th>Login</th>
                                                <th>Email</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $i = 1; foreach ($model->members as $member): ?>
                                                <tr>
                                                    <td><?= $i++ ?></td>
                                                    <td><?= Html::encode($member->login ?? '-') ?></td>
                                                    <td><?= Html::encode($member->email ?? '-') ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted mb-3">В этой группе пока нет сотрудников.</p>
                            <?php endif; ?>
                            
                            <?php if(Yii::$app->user->can('admin')): ?>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editMembersModal">
                                    <i class="bi bi-plus-lg"></i> Изменить состав
                                </button>
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

    <!-- Модальное окно редактирования состава -->
    <?php if(Yii::$app->user->can('admin')): ?>
    <div class="modal fade" id="editMembersModal" tabindex="-1" role="dialog" aria-labelledby="editMembersModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form method="post" action="<?= Yii::$app->urlManager->createUrl(['employee-group/add-members', 'id' => $model->id]) ?>">
                    <input type="hidden" name="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
                    <div class="modal-header">
                        <h4 class="modal-title" id="editMembersModalLabel">Изменить состав группы</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Выберите сотрудников:</label>
                            <select id="modal-employee-select" name="employee_ids[]" multiple="multiple" style="width: 100%;">
                                <?php foreach ($allEmployees as $employee): ?>
                                    <option value="<?= $employee->id ?>" <?= in_array($employee->id, $selectedIds) ? 'selected' : '' ?>>
                                        <?= Html::encode($employee->login) ?> (<?= Html::encode($employee->email) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-primary">Сохранить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
