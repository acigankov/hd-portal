<?php

use app\models\Organization;
use yii\helpers\Html;
use yii\web\View;

/* @var $this \yii\web\View */
/* @var $searchModel \app\models\OrganizationSearch */
/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $model Organization[]*/

$this->title = Yii::t('app', 'Organizations');
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
                        <li class="breadcrumb-item"><a href="#"><?= Yii::t('app', 'Home') ?></a></li>
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
                        <a class="btn btn-primary" href="<?= \yii\helpers\Url::to(['/organization/create'])?>" role="button"><?= Yii::t('app', 'New organization') ?></a>
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
                            <h3 class="card-title"><?= Yii::t('app', 'List of Organizations') ?></h3>

                        </div>
                        <!-- /.card-header -->
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover text-nowrap">
                                <thead>
                                <tr>
                                    <th><?= Yii::t('app', 'ID') ?></th>
                                    <th><?= Yii::t('app', 'Name') ?></th>
                                    <th><?= Yii::t('app', 'INN') ?></th>
                                    <th><?= Yii::t('app', 'KPP') ?></th>
                                    <th><?= Yii::t('app', 'Director') ?></th>
                                    <th><?= Yii::t('app', 'Phone') ?></th>
                                    <th><?= Yii::t('app', 'Email') ?></th>
                                    <th><?= Yii::t('app', 'Status') ?></th>
                                    <th><?= Yii::t('app', 'Actions') ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($dataProvider->getModels() as $item):?>
                                    <tr>
                                        <td><?= $item->id ?></td>
                                        <td><?= Html::a(Html::encode($item->name), ['/organization/view', 'id' => $item->id]) ?></td>
                                        <td><?= Html::encode($item->inn) ?></td>
                                        <td><?= Html::encode($item->kpp) ?></td>
                                        <td><?= Html::encode($item->director_name) ?></td>
                                        <td><?= Html::encode($item->phone) ?></td>
                                        <td><?= Html::encode($item->email) ?></td>
                                        <td>
                                            <?php
                                            $badgeClass = $item->status === Organization::STATUS_ACTIVE ? 'bg-success' : 'bg-secondary';
                                            ?>
                                            <span class="badge <?= $badgeClass ?>"><?= Html::encode($item->getStatusLabel()) ?></span>
                                        </td>
                                        <td>
                                            <?= Html::a(Yii::t('app', 'View'), Yii::$app->urlManager->createUrl(['organization/view', 'id' => $item->id]) , ['class' => 'btn btn-primary btn-sm' , 'role' => 'button']); ?>
                                            <?php if(Yii::$app->user->can('admin')): ?>
                                                <?= Html::a(Yii::t('app', 'Edit'), Yii::$app->urlManager->createUrl(['organization/update', 'id' => $item->id]) , ['class' => 'btn btn-primary btn-sm' , 'role' => 'button']); ?>
                                                <?= Html::button(Yii::t('app', 'Delete'), [
                                                    'class' => 'btn btn-danger btn-sm delete-btn',
                                                    'data-id' => $item->id,
                                                    'data-name' => $item->name,
                                                    'data-url' => Yii::$app->urlManager->createUrl(['organization/delete', 'id' => $item->id]),
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
                    <h4 class="modal-title" id="myModalLabel"><?= Yii::t('app', 'Confirm deletion') ?></h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?= Yii::t('app', 'Confirm deletion of group') ?>:  <span class="fw-bold fs-5" id="deleteUserName"></span>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= Yii::t('app', 'Cancel') ?></button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn"><?= Yii::t('app', 'Yes, delete') ?></button>
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
                    alert(response.message || '<?= Yii::t("app", "Error") ?>');
                    $('#confirmDeleteModal').modal('hide');
                }
            },
            error: function() {
                alert('<?= Yii::t("app", "Error") ?>');
                $('#confirmDeleteModal').modal('hide');
            }
        });
    });
JS
        , View::POS_END);
    ?>


</div>
