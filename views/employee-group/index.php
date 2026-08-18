<?php

use app\models\EmployeeGroup;
use yii\helpers\Html;
use yii\web\View;

/* @var $this \yii\web\View */
/* @var $model EmployeeGroup[]*/

$this->title = 'Группы сотрудников';
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
            <div class="row mb-4">
                <div class="col">
                    <?php if (Yii::$app->user->can('admin')): ?>
                        <a class="btn btn-primary" href="<?= \yii\helpers\Url::to(['/employee-group/create'])?>" role="button">Новая группа</a>
                    <?php endif; ?>
                </div>

            </div>
            <!--end::Row-->
            <!--begin::Row-->
            <div class="row">
                <!--begin::Col-->
                <div class="col">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Список Групп Сотрудников</h3>

                        </div>
                        <!-- /.card-header -->
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover text-nowrap">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Название</th>
                                    <th>Описание</th>
                                    <th>Организация</th>
                                    <th>Статус</th>
                                    <th>Действия</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($model as $item):?>
                                    <tr>
                                        <td><?= $item->id ?></td>
                                        <td><?= Html::a(Html::encode($item->name), ['/employee-group/view', 'id' => $item->id]) ?></td>
                                        <td><?= Html::encode(mb_substr($item->description ?? '', 0, 50)) ?></td>
                                        <td><?= Html::encode($item->organization?->name ?? '-') ?></td>
                                        <td>
                                            <?php
                                            $badgeClass = $item->status === EmployeeGroup::STATUS_ACTIVE ? 'bg-success' : 'bg-secondary';
                                            ?>
                                            <span class="badge <?= $badgeClass ?>"><?= Html::encode($item->getStatusLabel()) ?></span>
                                        </td>
                                        <td>
                                            <?= Html::a('Просмотр', Yii::$app->urlManager->createUrl(['employee-group/view', 'id' => $item->id]) , ['class' => 'btn btn-primary btn-sm' , 'role' => 'button']); ?>
                                            <?php if(Yii::$app->user->can('admin')): ?>
                                                <?= Html::a('Редактировать', Yii::$app->urlManager->createUrl(['employee-group/update', 'id' => $item->id]) , ['class' => 'btn btn-primary btn-sm' , 'role' => 'button']); ?>
                                                <?= Html::button('Удалить', [
                                                    'class' => 'btn btn-danger btn-sm delete-btn',
                                                    'data-id' => $item->id,
                                                    'data-name' => $item->name,
                                                    'data-url' => Yii::$app->urlManager->createUrl(['employee-group/delete', 'id' => $item->id]),
                                                    'data-toggle' => 'modal',
                                                    'data-target' => '#confirmDeleteModal',
                                                ]) ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>

                                <?php endforeach;?>

                                </tbody>
                            </table>
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

    <!-- Модальное окно ошибки -->
    <div class="modal fade" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="errorModalLabel">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="errorModalLabel"><i class="fas fa-exclamation-triangle me-2"></i>Ошибка</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="errorMessage" class="mb-0"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
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
                    // Показываем модальное окно с ошибкой
                    $('#errorMessage').text(response.message || 'Не удалось удалить группу');
                    $('#errorModal').modal('show');
                    $('#confirmDeleteModal').modal('hide');
                }
            },
            error: function(xhr) {
                var errorMsg = 'Произошла ошибка при удалении';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                // Показываем модальное окно с ошибкой
                $('#errorMessage').text(errorMsg);
                $('#errorModal').modal('show');
                $('#confirmDeleteModal').modal('hide');
            }
        });
    });
JS
        , View::POS_END);
    ?>


</div>
