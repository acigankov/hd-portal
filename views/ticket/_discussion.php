<?php

use app\models\TicketReply;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $replies app\models\TicketReply[] */
/* @var $attachments array<int, app\models\EmailAttachment[]> Файлы писем по записям обсуждения */

$attachments = $attachments ?? [];
?>

<?php if (empty($replies)) : ?>
    <p class="text-muted mb-0">Обсуждение пустое — ответов по заявке ещё не было.</p>
<?php endif; ?>

<?php foreach ($replies as $entry) : ?>
    <?php if ($entry->getIsSystem()) : ?>
        <div class="text-center my-3">
            <span class="badge bg-light text-dark border">
                <i class="bi bi-arrow-repeat"></i>
                Статус:
                <?= $entry->statusFrom !== null ? Html::encode($entry->statusFrom->name) : 'не указан' ?>
                &rarr;
                <strong><?= $entry->statusTo !== null ? Html::encode($entry->statusTo->name) : 'не указан' ?></strong>
                <span class="text-muted">
                    · <?= Html::encode($entry->getFormattedCreatedAt()) ?>
                    <?php if (!empty($entry->author_name)) : ?>
                        · <?= Html::encode($entry->author_name) ?>
                    <?php endif; ?>
                </span>
            </span>
        </div>
    <?php else : ?>
        <?php $fromOperator = $entry->getIsFromOperator(); ?>
        <div class="d-flex mb-3 <?= $fromOperator ? 'justify-content-end' : 'justify-content-start' ?>">
            <div class="p-3 rounded-3 <?= $fromOperator ? 'bg-primary-subtle border border-primary-subtle' : 'bg-body-secondary border' ?>"
                 style="max-width: 80%;">
                <div class="d-flex justify-content-between align-items-baseline gap-3 mb-1">
                    <strong class="small"><?= Html::encode($entry->getDisplayName()) ?></strong>
                    <span class="small text-muted text-nowrap">
                        <?= $fromOperator ? 'специалист' : 'заявитель' ?>,
                        <?= Html::encode($entry->getFormattedCreatedAt()) ?>
                    </span>
                </div>
                <div class="mb-0"><?= nl2br(Html::encode((string)$entry->text)) ?></div>

                <?= $this->render('_attachments', [
                    'attachments' => $attachments[$entry->id] ?? [],
                    'title' => $fromOperator ? 'Приложенные файлы' : 'Файлы из письма',
                ]) ?>

                <?php if ($fromOperator) : ?>
                    <div class="mt-2 small">
                        <?php if (!$entry->is_public) : ?>
                            <span class="badge bg-secondary">
                                <i class="bi bi-lock"></i> Внутренняя заметка
                            </span>
                        <?php elseif ($entry->email_status === TicketReply::EMAIL_SENT) : ?>
                            <span class="badge bg-success">
                                <i class="bi bi-envelope-check"></i>
                                Отправлено заявителю
                                <?php if (!empty($entry->email_sent_at)) : ?>
                                    · <?= Html::encode(Yii::$app->formatter->asDatetime($entry->email_sent_at, 'php:d.m.Y H:i')) ?>
                                <?php endif; ?>
                            </span>
                        <?php elseif ($entry->email_status === TicketReply::EMAIL_QUEUED) : ?>
                            <span class="badge bg-warning text-dark">
                                <i class="bi bi-hourglass-split"></i> В очереди на отправку
                            </span>
                        <?php elseif ($entry->email_status === TicketReply::EMAIL_FAILED) : ?>
                            <span class="badge bg-danger">
                                <i class="bi bi-exclamation-triangle"></i> Ошибка отправки письма
                            </span>
                        <?php else : ?>
                            <span class="badge bg-light text-dark border">
                                <i class="bi bi-chat-left-text"></i> Без отправки по email
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
<?php endforeach; ?>
