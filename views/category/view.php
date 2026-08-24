<?php

use yii\helpers\Html;
use yii\web\View;

/* @var $this \yii\web\View */
/* @var $model \app\models\Category */

$this->title = Yii::t('app', 'View category') . ': ' . Html::encode($model->name);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Categories'), 'url' => ['index']];
$this->params['breadcrumbs'][] = Yii::t('app', 'View');

?>

<div class="category-view">

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
                        <li class="breadcrumb-item"><a href="<?= \yii\helpers\Url::to(['index']) ?>"><?= Yii::t('app', 'Categories') ?></a></li>
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
                            <h3 class="card-title"><?= Yii::t('app', 'Category information') ?></h3>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <th style="width: 200px;"><?= $model->getAttributeLabel('id') ?></th>
                                        <td><?= $model->id ?></td>
                                    </tr>
                                    <tr>
                                        <th><?= $model->getAttributeLabel('name') ?></th>
                                        <td><?= Html::encode($model->name) ?></td>
                                    </tr>
                                    <tr>
                                        <th><?= $model->getAttributeLabel('code') ?></th>
                                        <td><code><?= Html::encode($model->code) ?></code></td>
                                    </tr>
                                    <tr>
                                        <th><?= $model->getAttributeLabel('description') ?></th>
                                        <td><?= nl2br(Html::encode($model->description)) ?></td>
                                    </tr>
                                    <tr>
                                        <th><?= $model->getAttributeLabel('color') ?></th>
                                        <td><span class="badge <?= $model->getColorBadgeClass() ?>"><?= Html::encode($model->color) ?></span></td>
                                    </tr>
                                    <tr>
                                        <th><?= $model->getAttributeLabel('icon') ?></th>
                                        <td><i class="bi <?= $model->getIconClass() ?>"></i> <?= Html::encode($model->icon ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <th><?= $model->getAttributeLabel('sort_order') ?></th>
                                        <td><?= $model->sort_order ?></td>
                                    </tr>
                                    <tr>
                                        <th><?= $model->getAttributeLabel('entity_types') ?></th>
                                        <td>
                                            <?php
                                            $entityTypes = [];
                                            if ($model->for_requests) $entityTypes[] = Yii::t('app', 'Requests');
                                            if ($model->for_tasks) $entityTypes[] = Yii::t('app', 'Tasks');
                                            if ($model->for_problems) $entityTypes[] = Yii::t('app', 'Problems');
                                            if (empty($entityTypes)) {
                                                echo '<span class="text-muted">' . Yii::t('app', 'Not specified') . '</span>';
                                            } else {
                                                echo implode(', ', $entityTypes);
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><?= $model->getAttributeLabel('status') ?></th>
                                        <td>
                                            <?php
                                            $badgeClass = $model->status === \app\models\Category::STATUS_ACTIVE ? 'bg-success' : 'bg-secondary';
                                            ?>
                                            <span class="badge <?= $badgeClass ?>"><?= Html::encode($model->getStatusLabel()) ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><?= $model->getAttributeLabel('created_at') ?></th>
                                        <td><?= $model->getFormattedCreatedAt() ?></td>
                                    </tr>
                                    <tr>
                                        <th><?= $model->getAttributeLabel('updated_at') ?></th>
                                        <td><?= $model->getFormattedUpdatedAt() ?></td>
                                    </tr>
                                    <tr>
                                        <th><?= $model->getAttributeLabel('created_by') ?></th>
                                        <td><?= $model->createdBy?->login ?? '-' ?></td>
                                    </tr>
                                    <tr>
                                        <th><?= $model->getAttributeLabel('updated_by') ?></th>
                                        <td><?= $model->updatedBy?->login ?? '-' ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <!-- /.card-body -->
                        <div class="card-footer">
                            <?php if(Yii::$app->user->can('admin')): ?>
                                <?= Html::a(Yii::t('app', 'Edit'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                            <?php endif; ?>
                            <?= Html::a(Yii::t('app', 'Back to list'), ['index'], ['class' => 'btn btn-secondary']) ?>
                        </div>
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
