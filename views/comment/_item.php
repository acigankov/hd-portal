<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $model \app\models\Comment */
/* @var $canEdit bool */
/* @var $canDelete bool */

$canEdit = $model->canEdit();
$canDelete = $model->canDelete();
$author = $model->author;
?>

<div class="comment-item border-bottom py-3" data-comment-id="<?= $model->id ?>">
    <div class="d-flex justify-content-between align-items-start mb-2">
        <div class="comment-author">
            <strong><?= Html::encode($author ? $author->login : Yii::t('app', 'Unknown')) ?></strong>
            <small class="text-muted ms-2">
                <?= Yii::$app->formatter->asDatetime($model->created_at, 'php:d.m.Y H:i') ?>
                <?php if ($model->is_edited): ?>
                    <span class="badge bg-secondary"><?= Yii::t('app', 'Edited') ?></span>
                <?php endif; ?>
            </small>
        </div>
        <div class="comment-actions">
            <?php if ($canEdit): ?>
                <button type="button" class="btn btn-sm btn-link comment-edit-btn" 
                        data-comment-id="<?= $model->id ?>" 
                        title="<?= Yii::t('app', 'Edit') ?>">
                    <i class="fas fa-edit"></i>
                </button>
            <?php endif; ?>
            <?php if ($canDelete): ?>
                <button type="button" class="btn btn-sm btn-link text-danger comment-delete-btn" 
                        data-comment-id="<?= $model->id ?>" 
                        title="<?= Yii::t('app', 'Delete') ?>">
                    <i class="fas fa-trash"></i>
                </button>
            <?php endif; ?>
        </div>
    </div>
    <div class="comment-body">
        <div class="comment-text"><?= nl2br(Html::encode($model->text)) ?></div>
        <textarea class="form-control comment-edit-textarea mt-2" style="display: none;"></textarea>
    </div>
    <?php if ($model->replies && count($model->replies) > 0): ?>
        <div class="comment-replies mt-3 ps-4 border-start">
            <?php foreach ($model->replies as $reply): ?>
                <?= $this->render('_item', ['model' => $reply]) ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
