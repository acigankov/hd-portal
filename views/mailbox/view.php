<?php

use app\models\EmailMessage;
use app\models\Mailbox;
use app\models\Ticket;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Mailbox */
/* @var $imapAvailable bool */

$this->title = 'Почтовый канал: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Почтовые каналы', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->name;

$encryptions = Mailbox::encryptionList();

// Небольшая сводка помогает понять, работает ли канал, не открывая логи.
$ticketsCount = Ticket::find()->andWhere(['mailbox_id' => $model->id])->count();
$queuedCount = EmailMessage::find()
    ->andWhere(['mailbox_id' => $model->id, 'status' => EmailMessage::STATUS_QUEUED])
    ->count();
$failedCount = EmailMessage::find()
    ->andWhere(['mailbox_id' => $model->id, 'status' => EmailMessage::STATUS_FAILED])
    ->count();
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6"><h3 class="mb-0"><?= Html::encode($this->title) ?></h3></div>
            <div class="col-sm-6 text-sm-end">
                <?= Html::a('Настроить', ['update', 'id' => $model->id], ['class' => 'btn btn-outline-secondary']) ?>
                <?= Html::a('Проверить подключение', ['test-connection', 'id' => $model->id], [
                    'class' => 'btn btn-outline-primary',
                    'data' => ['method' => 'post'],
                ]) ?>
                <?= Html::a('Забрать письма сейчас', ['fetch-now', 'id' => $model->id], [
                    'class' => 'btn btn-primary',
                    'data' => ['method' => 'post'],
                ]) ?>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <?php if (!$imapAvailable) : ?>
            <div class="alert alert-warning">
                Расширение PHP <code>imap</code> не установлено — письма приниматься не будут.
            </div>
        <?php endif; ?>

        <?php if (!empty($model->last_error)) : ?>
            <div class="alert alert-danger">
                Последняя ошибка обработки: <?= Html::encode($model->last_error) ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header"><h3 class="card-title mb-0">Канал</h3></div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <tbody>
                            <tr>
                                <th class="text-muted fw-normal" style="width: 45%;">Адрес ящика</th>
                                <td><?= Html::encode($model->email) ?></td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-normal">Состояние</th>
                                <td>
                                    <?= $model->is_active
                                        ? '<span class="badge bg-success">активен</span>'
                                        : '<span class="badge bg-secondary">выключен</span>' ?>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-normal">Имя отправителя</th>
                                <td><?= Html::encode($model->getFromNameEffective()) ?></td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-normal">Адрес для ответов</th>
                                <td><?= Html::encode($model->getReplyToEffective()) ?></td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-normal">IMAP</th>
                                <td>
                                    <?= Html::encode($model->imap_host . ':' . $model->imap_port) ?>
                                    (<?= Html::encode($encryptions[$model->imap_encryption] ?? $model->imap_encryption) ?>),
                                    папка <?= Html::encode($model->imap_folder) ?>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-normal">SMTP</th>
                                <td>
                                    <?= $model->getCanSend()
                                        ? Html::encode($model->smtp_host . ':' . $model->smtp_port)
                                        : '<span class="text-muted">не настроен, канал только принимает письма</span>' ?>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-normal">Последняя проверка</th>
                                <td>
                                    <?= !empty($model->last_checked_at)
                                        ? Html::encode(Yii::$app->formatter->asDatetime($model->last_checked_at, 'php:d.m.Y H:i:s'))
                                        : '<span class="text-muted">ещё не проверялся</span>' ?>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-normal">Последний UID</th>
                                <td><?= (int)$model->last_uid ?></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header"><h3 class="card-title mb-0">Маршрутизация новых заявок</h3></div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <tbody>
                            <tr>
                                <th class="text-muted fw-normal" style="width: 45%;">Организация</th>
                                <td><?= $model->defaultOrganization !== null ? Html::encode($model->defaultOrganization->name) : '—' ?></td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-normal">Категория</th>
                                <td><?= $model->defaultCategory !== null ? Html::encode($model->defaultCategory->name) : '—' ?></td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-normal">Статус новой заявки</th>
                                <td><?= $model->defaultStatus !== null ? Html::encode($model->defaultStatus->name) : 'статус по умолчанию' ?></td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-normal">Статус при ответе заявителя</th>
                                <td><?= $model->reopenStatus !== null ? Html::encode($model->reopenStatus->name) : 'не меняется' ?></td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-normal">Приоритет</th>
                                <td><?= Html::encode(Ticket::priorityList()[$model->default_priority] ?? '—') ?></td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-normal">Авторы из писем</th>
                                <td><?= $model->create_authors ? 'добавляются в справочник' : 'не добавляются' ?></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header"><h3 class="card-title mb-0">Статистика канала</h3></div>
                    <div class="card-body">
                        <p class="mb-1">Заявок создано из этого ящика: <strong><?= (int)$ticketsCount ?></strong></p>
                        <p class="mb-1">Писем в очереди на отправку: <strong><?= (int)$queuedCount ?></strong></p>
                        <p class="mb-0">
                            Писем с ошибкой отправки:
                            <strong class="<?= $failedCount > 0 ? 'text-danger' : '' ?>"><?= (int)$failedCount ?></strong>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
