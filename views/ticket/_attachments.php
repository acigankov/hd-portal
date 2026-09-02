<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $attachments app\models\EmailAttachment[] */
/* @var $title string|null */

if (empty($attachments)) {
    return;
}
?>

<div class="mt-2 small">
    <div class="text-muted mb-1">
        <i class="bi bi-paperclip"></i>
        <?= Html::encode($title ?? 'Файлы из письма') ?>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <?php foreach ($attachments as $attachment) : ?>
            <?= Html::a(
                '<i class="' . $attachment->getIconClass() . '"></i> '
                    . Html::encode($attachment->original_name)
                    . ' <span class="text-muted">(' . Html::encode($attachment->getFormattedSize()) . ')</span>',
                ['attachment', 'id' => $attachment->id],
                [
                    'class' => 'btn btn-sm btn-outline-secondary',
                    'title' => 'Скачать файл',
                ]
            ) ?>
        <?php endforeach; ?>
    </div>
</div>
