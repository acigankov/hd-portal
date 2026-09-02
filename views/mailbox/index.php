<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel app\models\MailboxSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $imapAvailable bool */

$this->title = 'Почтовые каналы';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6"><h3 class="mb-0"><?= Html::encode($this->title) ?></h3></div>
            <div class="col-sm-6 text-sm-end">
                <?= Html::a('<i class="bi bi-plus-lg"></i> Подключить ящик', ['create'], ['class' => 'btn btn-primary']) ?>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <?php if (!$imapAvailable) : ?>
            <div class="alert alert-warning">
                Расширение PHP <code>imap</code> не установлено — приём писем работать не будет.
                Установите его на сервере приложения и перезапустите PHP.
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title mb-0">Ящики, из которых регистрируются заявки</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                    <tr>
                        <th>Канал</th>
                        <th>Адрес</th>
                        <th>Состояние</th>
                        <th>Отправка</th>
                        <th>Последняя проверка</th>
                        <th class="text-end">Действия</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($dataProvider->getModels() as $item) : ?>
                        <tr>
                            <td><?= Html::a(Html::encode($item->name), ['view', 'id' => $item->id]) ?></td>
                            <td><?= Html::encode($item->email) ?></td>
                            <td>
                                <?php if ($item->is_active) : ?>
                                    <span class="badge bg-success">активен</span>
                                <?php else : ?>
                                    <span class="badge bg-secondary">выключен</span>
                                <?php endif; ?>
                                <?php if (!empty($item->last_error)) : ?>
                                    <span class="badge bg-danger" title="<?= Html::encode($item->last_error) ?>">ошибка</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= $item->getCanSend()
                                    ? '<span class="badge bg-primary">SMTP настроен</span>'
                                    : '<span class="badge bg-light text-dark border">только приём</span>' ?>
                            </td>
                            <td>
                                <?= !empty($item->last_checked_at)
                                    ? Html::encode(Yii::$app->formatter->asDatetime($item->last_checked_at, 'php:d.m.Y H:i'))
                                    : '<span class="text-muted">ещё не проверялся</span>' ?>
                            </td>
                            <td class="text-end text-nowrap">
                                <?= Html::a('Открыть', ['view', 'id' => $item->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                                <?= Html::a('Настроить', ['update', 'id' => $item->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($dataProvider->getModels())) : ?>
                        <tr>
                            <td colspan="6" class="text-muted p-3">
                                Ящики не подключены. Добавьте первый канал —
                                <?= Html::a('подключить ящик', Url::to(['create'])) ?>.
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer small text-muted">
                Письма забираются командой <code>php yii mail/fetch-all</code>, ответы отправляются
                командой <code>php yii mail/send-pending</code> — обе запускаются по cron раз в минуту.
            </div>
        </div>
    </div>
</div>
