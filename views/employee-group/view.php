<?php

use app\models\EmployeeGroup;
use yii\helpers\Html;
use yii\web\View;

/* @var $this \yii\web\View */
/* @var $model EmployeeGroup*/

$this->title = 'Просмотр группы сотрудников';
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
                            <h3 class="card-title">Информация о группе</h3>

                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <p><strong>ID:</strong> <?= $model->id ?> </p>
                            <p><strong>Название:</strong> <?= Html::encode($model->name) ?> </p>
                            <p><strong>Описание:</strong> <?= Html::encode($model->description ?? '-') ?> </p>
                            <p><strong>Организация:</strong> <?= Html::encode($model->organization?->name ?? '-') ?> </p>
                            <p><strong>Статус:</strong> 
                                <?php
                                $badgeClass = $model->status === EmployeeGroup::STATUS_ACTIVE ? 'bg-success' : 'bg-secondary';
                                ?>
                                <span class="badge <?= $badgeClass ?>"><?= Html::encode($model->getStatusLabel()) ?></span>
                            </p>
                            <p><strong>Дата создания:</strong> <?= $model->getFormattedCreatedAt() ?> </p>
                            <p><strong>Дата обновления:</strong> <?= $model->getFormattedUpdatedAt() ?> </p>
                        </div>
                        <!-- /.card-body -->
                        <div class="card-footer">
                            <?= Html::a('Редактировать', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                            <?= Html::a('Назад к списку', ['index'], ['class' => 'btn btn-secondary']) ?>
                            <?php if(Yii::$app->user->can('admin')): ?>
                                <?= Html::button('Удалить', [
                                    'class' => 'btn btn-danger',
                                    'data-id' => $model->id,
                                    'data-name' => $model->name,
                                    'data-url' => Yii::$app->urlManager->createUrl(['employee-group/delete', 'id' => $model->id]),
                                    'data-toggle' => 'modal',
                                    'data-target' => '#confirmDeleteModal',
                                ]) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <!--end::Col-->
            </div>
            <!--end::Row-->
            
            <!--begin::Row - Сотрудники группы-->
            <div class="row">
                <!--begin::Col-->
                <div class="col">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Сотрудники группы</h3>
                            <?php if(Yii::$app->user->can('admin')): ?>
                                <button type="button" class="btn btn-sm btn-primary ms-auto" data-bs-toggle="modal" data-bs-target="#editMembersModal">
                                    <i class="bi bi-plus-lg"></i> Изменить состав
                                </button>
                            <?php endif; ?>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <?php if (!empty($model->members)): ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th style="width: 50px;">#</th>
                                                <th>ФИО</th>
                                                <th>Login</th>
                                                <th>Email</th>
                                                <th style="width: 150px;">Дата добавления</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $i = 1; foreach ($model->members as $member): ?>
                                                <tr>
                                                    <td><?= $i++ ?></td>
                                                    <td><?= Html::encode($member->login ?? '-') ?></td>
                                                    <td><?= Html::encode($member->login ?? '-') ?></td>
                                                    <td><?= Html::encode($member->email ?? '-') ?></td>
                                                    <td><?= $model->getFormattedCreatedAt() ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted mb-0">В этой группе пока нет сотрудников.</p>
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
                            <div class="border rounded p-3" style="max-height: 400px; overflow-y: auto;">
                                <?php foreach ($allEmployees as $employee): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                               name="employee_ids[]" 
                                               value="<?= $employee->id ?>" 
                                               id="employee_<?= $employee->id ?>"
                                               <?= in_array($employee->id, $currentMemberIds) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="employee_<?= $employee->id ?>">
                                            <?= Html::encode($employee->login) ?> (<?= Html::encode($employee->email) ?>)
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
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

    <!-- Модальное окно подтверждения -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel">Подтверждение удаления</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Подтвердите удаление группы:  <span class="fw-bold fs-5" id="deleteUserName"></span>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Да, удалить</button>
                </div>
            </div>
        </div>
    </div>

    <?php
    $csrfToken = Yii::$app->request->csrfToken;

    $this->registerJs(<<<JS
    var deleteUrl = '';
    var csrfToken = '{$csrfToken}';
    
    // При клике на кнопку удаления — открываем модалку
    $('.delete-btn').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        deleteUrl = $(this).data('url');
        var name = $(this).data('name');
        $('#deleteUserName').text(name);
        $('#confirmDeleteModal').modal('show');
    });
    
    // При клике на "Да, удалить"
    $('#confirmDeleteBtn').on('click', function() {
        if (!deleteUrl) return;
        
        $.ajax({
            url: deleteUrl,
            type: 'POST',
            data: {
                _csrf: csrfToken
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.message || 'Не удалось удалить группу');
                    $('#confirmDeleteModal').modal('hide');
                }
            },
            error: function() {
                alert('Произошла ошибка при удалении');
                $('#confirmDeleteModal').modal('hide');
            }
        });
    });
JS
        , View::POS_END);
    ?>


</div>
