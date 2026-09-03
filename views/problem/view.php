<?php

use yii\helpers\Html;
use yii\web\View;

/* @var $this \yii\web\View */
/* @var $model \app\models\Problem */


$this->title = Yii::t('app', 'View problem') . ': ' . Html::encode($model->problem_number);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Problems'), 'url' => ['index']];
$this->params['breadcrumbs'][] = Yii::t('app', 'View');

$status = $model->getStatusById($model->status_id);

?>

<div class="problem-view">

    <!--begin::App Content Header-->
    <div class="app-content-header">
        <!--begin::Container-->
        <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0"><?php echo Html::encode($this->title); ?></h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#"><?= Yii::t('app', 'Home') ?></a></li>
                        <li class="breadcrumb-item"><a href="<?= \yii\helpers\Url::to(['index']) ?>"><?= Yii::t('app', 'Problems') ?></a></li>
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
        <div class="container-fluid mb-4">
            <!--begin::Row-->
            <div class="row">
                <!--begin::Col-->
                <div class="col">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><?= Yii::t('app', 'Problem information') ?></h3>
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
                                        <th><?= $model->getAttributeLabel('problem_number') ?></th>
                                        <td><span class="badge bg-info"><?= Html::encode($model->problem_number) ?></span></td>
                                    </tr>
                                    <tr>
                                        <th><?= $model->getAttributeLabel('title') ?></th>
                                        <td><?= Html::encode($model->title) ?></td>
                                    </tr>
                                    <tr>
                                        <th><?= $model->getAttributeLabel('description') ?></th>
                                        <td><?= nl2br(Html::encode($model->description)) ?></td>
                                    </tr>
                                    <tr>
                                        <th><?= $model->getAttributeLabel('jira_ticket') ?></th>
                                        <td>
                                            <?php if (!empty($model->jira_ticket)): ?>
                                                <a href="<?= Html::encode($model->jira_ticket) ?>" target="_blank" rel="noopener noreferrer">
                                                    <i class="fas fa-external-link-alt"></i> <?= Html::encode($model->jira_ticket) ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted"><?= Yii::t('app', 'Not specified') ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><?= $model->getAttributeLabel('category_id') ?></th>
                                        <td>
                                            <?php if ($model->category): ?>
                                                <span class="badge bg-<?= $model->category->color ?>">
                                                    <?= Html::encode($model->category->name) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted"><?= Yii::t('app', 'Not specified') ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><?= $model->getAttributeLabel('status_id') ?></th>
                                        <td>
                                            <?php if ($status): ?>
                                                <span class="badge bg-<?= $status->color ?>">
                                                    <?= Html::encode($status->name) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted"><?= Yii::t('app', 'Not specified') ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><?= $model->getAttributeLabel('priority') ?></th>
                                        <td>
                                            <span class="badge bg-<?= $model->getPriorityColor() ?>">
                                                <?= $model->getPriorityLabel() ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><?= $model->getAttributeLabel('author_id') ?></th>
                                        <td>
                                            <?php if ($model->author): ?>
                                                <?= Html::encode($model->author->fullName) ?>
                                            <?php else: ?>
                                                <span class="text-muted"><?= Yii::t('app', 'Not specified') ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><?= $model->getAttributeLabel('responsible_id') ?></th>
                                        <td>
                                            <?php if ($model->responsible): ?>
                                                <?= Html::encode($model->responsible->login) ?>
                                            <?php else: ?>
                                                <span class="text-muted"><?= Yii::t('app', 'Not specified') ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><?= $model->getAttributeLabel('due_date') ?></th>
                                        <td>
                                            <?php if ($model->due_date): ?>
                                                <?= date('d.m.Y', strtotime($model->due_date)) ?>
                                            <?php else: ?>
                                                <span class="text-muted"><?= Yii::t('app', 'Not specified') ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><?= $model->getAttributeLabel('created_at') ?></th>
                                        <td><?= $model->created_at ?></td>
                                    </tr>
                                    <tr>
                                        <th><?= $model->getAttributeLabel('updated_at') ?></th>
                                        <td><?= $model->updated_at ?? '-' ?></td>
                                    </tr>
                                    <tr>
                                        <th><?= $model->getAttributeLabel('created_by') ?></th>
                                        <td><?= $model->createdBy?->login ?? '-' ?></td>
                                    </tr>
                                    <tr>
                                        <th><?= $model->getAttributeLabel('updated_by') ?></th>
                                        <td><?= $model->updatedBy?->login ?? '-' ?></td>
                                    </tr>
                                    <tr>
                                        <th><?= Yii::t('app', 'Related Tickets') ?></th>
                                        <td>
                                            <?php if (!empty($model->tickets)): ?>
                                                <ul class="list-unstyled mb-0">
                                                    <?php foreach ($model->tickets as $ticket): ?>
                                                        <li>
                                                            <a href="<?= \yii\helpers\Url::to(['/ticket/view', 'id' => $ticket->id]) ?>">
                                                                <span class="badge bg-info"><?= Html::encode($ticket->ticket_number) ?></span>
                                                                <?= Html::encode($ticket->subject) ?>
                                                            </a>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php else: ?>
                                                <span class="text-muted"><?= Yii::t('app', 'No related tickets') ?></span>
                                            <?php endif; ?>
                                        </td>
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
        <!--begin::Container-->
        <div class="container-fluid">
            <?= \app\widgets\CommentsWidget::widget([
                    'modelClass' => get_class($model),
                'modelId' => $model->id,
                'title' => Yii::t('app', 'Comments'),
                ]) ?>
        </div>
        <!--end::Container-->
    </div>
    <!--end::App Content-->
</div>
