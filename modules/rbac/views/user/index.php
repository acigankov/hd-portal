<?php

use app\models\User;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/* @var $this \yii\web\View */
/* @var $gridViewColumns array */
/* @var $dataProvider \yii\data\ArrayDataProvider */
/* @var $searchModel \app\modules\rbac\models\search\AssignmentSearch */
/* @var $model User*/

$this->title = Yii::t('yii2mod.rbac', 'Users');
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
                    <a class="btn btn-primary" href="<?= Url::to('/rbac/user/create')?>" role="button">Новый пользователь</a>
                </div>

            </div>
            <!--end::Row-->
            <!--begin::Row-->
            <div class="row">
                <!--begin::Col-->
                <div class="col">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Список Пользователей</h3>

                        </div>
                        <!-- /.card-header -->
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover text-nowrap">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Логин</th>
                                    <th>E-mail</th>
                                    <th>Создан</th>
                                    <th>Статус</th>
                                    <th>Действия</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($model as $item):?>
                                    <tr>
                                        <td><?= $item->id ?></td>
                                        <td><?= $item->login ?></td>
                                        <td><?= $item->email ?></td>
                                        <td><?= $item->formattedCreatedAt ?></td>
                                        <td><?= $item->status == 1 ? 'Активен' : 'Неактивен' ?></td>
                                        <td>
                                            <?php if($item->id > 1):?>
                                             
                                                <?= Html::button('Удалить', [
                                                    'class' => 'btn btn-danger btn-sm delete-btn',
                                                    'data-id' => $item->id,
                                                    'data-name' => $item->login,
                                                    'data-url' => Yii::$app->urlManager->createUrl(['rbac/user/delete', 'id' => $item->id]),
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
<!--    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">-->
<!--      <div class="modal-dialog modal-dialog-centered" role="document">-->
<!--        <div class="modal-content">-->
<!--          <div class="modal-header d-flex justify-content-between">-->
<!--            <h4 class="modal-title" id="myModalLabel">Подтверждение удаления</h4>-->
<!--            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>-->
<!---->
<!--          </div>-->
<!--          <div class="modal-body">-->
<!--            Вы действительно хотите удалить пользователя <strong id="deleteUserName"></strong>?-->
<!--          </div>-->
<!--          <div class="modal-footer">-->
<!--            <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>-->
<!--            <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Да, удалить</button>-->
<!--          </div>-->
<!--        </div>-->
<!--      </div>-->
<!--    </div>-->
<!---->

<!--    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">-->
<!--        Launch demo modal-->
<!--    </button>-->

    <!-- Modal -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel">Подтверждение удаления</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Подтвердите удаление пользователя:  <span class="fw-bold fs-5" id="deleteUserName"></span>
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
                    alert(response.message || 'Не удалось удалить пользователя');
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
