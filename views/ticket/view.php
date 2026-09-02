<?php

use app\components\mail\AttachmentPolicy;
use app\models\TicketReply;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Ticket */
/* @var $replies app\models\TicketReply[] */
/* @var $reply app\models\TicketReply */
/* @var $statuses app\models\Status[] */
/* @var $canProcess bool */
/* @var $canEmailAuthor bool */
/* @var $attachments array<int, app\models\EmailAttachment[]> Файлы писем: ключ 0 — исходное письмо */

$attachments = $attachments ?? [];

$this->title = 'Заявка ' . $model->ticket_number;
?>

<!--begin::App Content Header-->
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0"><?= Html::encode($model->ticket_number) ?> · <?= Html::encode($model->subject) ?></h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="<?= Url::to(['/site/index']) ?>">Главная</a></li>
                    <li class="breadcrumb-item"><a href="<?= Url::to(['index']) ?>">Заявки</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= Html::encode($model->ticket_number) ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>
<!--end::App Content Header-->

<!--begin::App Content-->
<div class="app-content">
    <div class="container-fluid">

        <?php foreach (['success' => 'alert-success', 'error' => 'alert-danger'] as $flashKey => $flashClass) : ?>
            <?php if (Yii::$app->session->hasFlash($flashKey)) : ?>
                <div class="alert <?= $flashClass ?> alert-dismissible fade show">
                    <?= Html::encode(Yii::$app->session->getFlash($flashKey)) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Закрыть"></button>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">Обращение</h3>
                        <div class="d-flex gap-2">
                            <?php if ($canProcess) : ?>
                                <?= Html::a('<i class="bi bi-pencil"></i> Редактировать', ['update', 'id' => $model->id], [
                                    'class' => 'btn btn-sm btn-outline-primary',
                                ]) ?>
                            <?php endif; ?>
                            <?php if (\app\models\User::currentHasRole('admin')) : ?>
                                <?= Html::a('<i class="bi bi-trash"></i>', ['delete', 'id' => $model->id], [
                                    'class' => 'btn btn-sm btn-outline-danger',
                                    'title' => 'Удалить заявку',
                                    'data' => [
                                        'confirm' => 'Удалить заявку вместе со всем обсуждением?',
                                        'method' => 'post',
                                    ],
                                ]) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5><?= Html::encode($model->subject) ?></h5>
                        <?php if (!empty($model->description)) : ?>
                            <div class="mb-0"><?= nl2br(Html::encode($model->description)) ?></div>
                        <?php else : ?>
                            <p class="text-muted mb-0">Описание не заполнено.</p>
                        <?php endif; ?>

                        <?= $this->render('_attachments', [
                            'attachments' => $attachments[0] ?? [],
                            'title' => 'Файлы из первого письма',
                        ]) ?>
                    </div>
                </div>

                <div class="card mt-3" id="discussion">
                    <div class="card-header">
                        <h3 class="card-title mb-0">Обсуждение</h3>
                    </div>
                    <div class="card-body">
                        <?= $this->render('_discussion', [
                            'replies' => $replies,
                            'attachments' => $attachments,
                        ]) ?>
                    </div>

                    <?php if ($canProcess) : ?>
                        <div class="card-footer">
                            <?php $form = ActiveForm::begin([
                                'action' => ['reply', 'id' => $model->id],
                                'id' => 'ticket-reply-form',
                                // Без multipart файлы до сервера не дойдут.
                                'options' => ['enctype' => 'multipart/form-data'],
                                'fieldConfig' => [
                                    'options' => ['class' => 'mb-2'],
                                    'labelOptions' => ['class' => 'form-label'],
                                ],
                            ]); ?>

                            <?= $form->field($reply, 'text')
                                ->textarea(['rows' => 4, 'placeholder' => 'Текст ответа'])
                                ->label('Ответ') ?>

                            <?= $form->field($reply, 'uploadedFiles[]')
                                ->fileInput(['multiple' => true, 'class' => 'form-control'])
                                ->hint(
                                    'До ' . AttachmentPolicy::MAX_COUNT . ' файлов, каждый до '
                                    . AttachmentPolicy::maxFileSizeMb()
                                    . ' МБ. Исполняемые файлы не принимаются. '
                                    . 'При ответе заявителю файлы уйдут вместе с письмом.'
                                )
                                ->label('Файлы') ?>

                            <div class="row g-2 align-items-end">
                                <div class="col-md-4">
                                    <?= $form->field($reply, 'author_side')
                                        ->dropDownList([
                                            TicketReply::SIDE_OPERATOR => 'От специалиста',
                                            TicketReply::SIDE_CLIENT => 'От заявителя',
                                        ])
                                        ->label('Сторона') ?>
                                </div>
                                <div class="col-md-4">
                                    <?= $form->field($reply, 'is_public')
                                        ->dropDownList([
                                            0 => 'Внутренняя заметка',
                                            1 => 'Ответ заявителю (email)',
                                        ], ['disabled' => !$canEmailAuthor])
                                        ->label('Тип сообщения') ?>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <?= Html::submitButton('<i class="bi bi-send"></i> Сохранить', ['class' => 'btn btn-primary']) ?>
                                </div>
                            </div>

                            <div class="small text-muted">
                                <?php if ($canEmailAuthor) : ?>
                                    Ответ заявителю уйдёт на
                                    <strong><?= Html::encode((string)$model->author_email) ?></strong>
                                    с адреса <strong><?= Html::encode((string)$model->mailbox->email) ?></strong>.
                                    Внутреннюю заметку заявитель не увидит.
                                <?php elseif (empty($model->author_email)) : ?>
                                    У заявителя не указан email, поэтому ответ сохраняется только в портале.
                                <?php else : ?>
                                    Заявка не связана с почтовым каналом с настроенной отправкой —
                                    выберите канал в редактировании заявки, чтобы ответы уходили по email.
                                <?php endif; ?>
                            </div>

                            <?php ActiveForm::end(); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mt-3">
                    <?= \app\widgets\CommentsWidget::widget([
                        'modelClass' => \app\models\Ticket::class,
                        'modelId' => $model->id,
                        'title' => 'Комментарии',
                    ]) ?>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title mb-0">Статус</h3>
                    </div>
                    <div class="card-body">
                        <p class="mb-3">
                            <?php if ($model->ticketStatus !== null) : ?>
                                <span class="badge <?= Html::encode($model->ticketStatus->getColorBadgeClass()) ?> fs-6">
                                    <?= Html::encode($model->ticketStatus->name) ?>
                                </span>
                            <?php else : ?>
                                <span class="text-muted">Статус не указан</span>
                            <?php endif; ?>
                        </p>

                        <?php if ($canProcess) : ?>
                            <form method="post" action="<?= Url::to(['change-status', 'id' => $model->id]) ?>" class="d-flex gap-2">
                                <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>"
                                       value="<?= Html::encode(Yii::$app->request->csrfToken) ?>">
                                <?= Html::dropDownList('status_id', $model->status_id, ArrayHelper::map($statuses, 'id', 'name'), [
                                    'class' => 'form-select',
                                    'prompt' => 'Выберите статус',
                                ]) ?>
                                <button type="submit" class="btn btn-primary text-nowrap">Сменить</button>
                            </form>
                            <div class="form-text">Каждая смена статуса попадает в обсуждение.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h3 class="card-title mb-0">Данные заявки</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <tbody>
                            <tr>
                                <th class="text-muted fw-normal" style="width: 45%;">Приоритет</th>
                                <td>
                                    <span class="badge <?= Html::encode($model->getPriorityBadgeClass()) ?>">
                                        <?= Html::encode($model->getPriorityLabel()) ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-normal">Категория</th>
                                <td><?= $model->category !== null ? Html::encode($model->category->name) : '—' ?></td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-normal">Организация</th>
                                <td><?= $model->organization !== null ? Html::encode($model->organization->name) : '—' ?></td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-normal">Почтовый канал</th>
                                <td>
                                    <?php if ($model->mailbox !== null) : ?>
                                        <?= Html::encode($model->mailbox->name) ?>
                                        <span class="text-muted">&lt;<?= Html::encode($model->mailbox->email) ?>&gt;</span>
                                    <?php else : ?>
                                        <span class="text-muted">заявка создана вручную</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-normal">Специалист</th>
                                <td>
                                    <?= $model->assigned !== null
                                        ? Html::encode(TicketReply::userName($model->assigned))
                                        : '<span class="text-muted">не назначен</span>' ?>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-normal">Создана</th>
                                <td><?= Html::encode($model->getFormattedCreatedAt()) ?></td>
                            </tr>
                            <?php if (!empty($model->updated_at)) : ?>
                                <tr>
                                    <th class="text-muted fw-normal">Изменена</th>
                                    <td><?= Html::encode($model->getFormattedUpdatedAt()) ?></td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h3 class="card-title mb-0">Автор обращения</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <tbody>
                            <tr>
                                <th class="text-muted fw-normal" style="width: 45%;">ФИО</th>
                                <td><?= Html::encode($model->getAuthorDisplayName()) ?></td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-normal">Email</th>
                                <td>
                                    <?= !empty($model->author_email)
                                        ? Html::a(Html::encode($model->author_email), 'mailto:' . $model->author_email)
                                        : '—' ?>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-normal">Телефон</th>
                                <td><?= !empty($model->author_phone) ? Html::encode($model->author_phone) : '—' ?></td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-normal">В справочнике</th>
                                <td>
                                    <?= $model->author !== null
                                        ? Html::a(Html::encode($model->author->full_name), ['/author/view', 'id' => $model->author->id])
                                        : '<span class="text-muted">нет записи</span>' ?>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--end::App Content-->
