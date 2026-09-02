<?php

use app\models\Ticket;
use app\models\TicketReply;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

/* @var $this yii\web\View */
/* @var $searchModel app\models\TicketSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $organizations app\models\Organization[] */
/* @var $categories app\models\Category[] */
/* @var $statuses app\models\Status[] */
/* @var $users app\models\User[] */

$this->title = 'Заявки';

$tickets = $dataProvider->getModels();
$canProcess = \app\models\User::canProcessTickets();
?>

<!--begin::App Content Header-->
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6"><h3 class="mb-0"><?= Html::encode($this->title) ?></h3></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="<?= Url::to(['/site/index']) ?>">Главная</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= Html::encode($this->title) ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>
<!--end::App Content Header-->

<!--begin::App Content-->
<div class="app-content">
    <div class="container-fluid">

        <?php if (Yii::$app->session->hasFlash('success')) : ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= Html::encode(Yii::$app->session->getFlash('success')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Закрыть"></button>
            </div>
        <?php endif; ?>

        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">Фильтр</h3>
                <?php if ($canProcess) : ?>
                    <?= Html::a('<i class="bi bi-plus-lg"></i> Новая заявка', ['create'], ['class' => 'btn btn-primary btn-sm']) ?>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <form method="get" action="<?= Url::to(['index']) ?>" class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label" for="filter-subject">Тема или описание</label>
                        <input type="text" class="form-control" id="filter-subject"
                               name="TicketSearch[subject]"
                               value="<?= Html::encode((string)$searchModel->subject) ?>"
                               placeholder="Текст обращения">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="filter-number">Номер</label>
                        <input type="text" class="form-control" id="filter-number"
                               name="TicketSearch[ticket_number]"
                               value="<?= Html::encode((string)$searchModel->ticket_number) ?>"
                               placeholder="tkt#000001">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="filter-organization">Организация</label>
                        <?= Html::dropDownList(
                            'TicketSearch[organization_id]',
                            $searchModel->organization_id,
                            ArrayHelper::map($organizations, 'id', 'name'),
                            ['class' => 'form-select', 'id' => 'filter-organization', 'prompt' => 'Все']
                        ) ?>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="filter-status">Статус</label>
                        <?= Html::dropDownList(
                            'TicketSearch[status_id]',
                            $searchModel->status_id,
                            ArrayHelper::map($statuses, 'id', 'name'),
                            ['class' => 'form-select', 'id' => 'filter-status', 'prompt' => 'Все']
                        ) ?>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="filter-priority">Приоритет</label>
                        <?= Html::dropDownList(
                            'TicketSearch[priority]',
                            $searchModel->priority,
                            Ticket::priorityList(),
                            ['class' => 'form-select', 'id' => 'filter-priority', 'prompt' => 'Любой']
                        ) ?>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="filter-category">Категория</label>
                        <?= Html::dropDownList(
                            'TicketSearch[category_id]',
                            $searchModel->category_id,
                            ArrayHelper::map($categories, 'id', 'name'),
                            ['class' => 'form-select', 'id' => 'filter-category', 'prompt' => 'Все']
                        ) ?>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="filter-assigned">Специалист</label>
                        <?= Html::dropDownList(
                            'TicketSearch[assigned_id]',
                            $searchModel->assigned_id,
                            ArrayHelper::map($users, 'id', static function ($user) {
                                return TicketReply::userName($user);
                            }),
                            ['class' => 'form-select', 'id' => 'filter-assigned', 'prompt' => 'Все']
                        ) ?>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="filter-author">Автор обращения</label>
                        <input type="text" class="form-control" id="filter-author"
                               name="TicketSearch[author_name]"
                               value="<?= Html::encode((string)$searchModel->author_name) ?>"
                               placeholder="ФИО">
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Найти</button>
                        <?= Html::a('Сбросить', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    Найдено заявок: <?= (int)$dataProvider->getTotalCount() ?>
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                        <tr>
                            <th style="width: 120px;">Номер</th>
                            <th>Тема</th>
                            <th style="width: 180px;">Организация</th>
                            <th style="width: 160px;">Автор</th>
                            <th style="width: 160px;">Специалист</th>
                            <th style="width: 130px;">Статус</th>
                            <th style="width: 110px;">Приоритет</th>
                            <th style="width: 130px;">Создана</th>
                            <th style="width: 90px;"></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($tickets)) : ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">Заявок не найдено</td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach ($tickets as $ticket) : ?>
                            <tr>
                                <td>
                                    <?= Html::a(Html::encode($ticket->ticket_number), ['view', 'id' => $ticket->id], [
                                        'class' => 'text-decoration-none',
                                    ]) ?>
                                </td>
                                <td>
                                    <?= Html::a(Html::encode($ticket->subject), ['view', 'id' => $ticket->id], [
                                        'class' => 'text-decoration-none fw-semibold',
                                    ]) ?>
                                    <?php if ($ticket->category !== null) : ?>
                                        <div class="small text-muted"><?= Html::encode($ticket->category->name) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><?= $ticket->organization !== null ? Html::encode($ticket->organization->name) : '—' ?></td>
                                <td><?= Html::encode($ticket->getAuthorDisplayName()) ?></td>
                                <td><?= $ticket->assigned !== null ? Html::encode(TicketReply::userName($ticket->assigned)) : '<span class="text-muted">не назначен</span>' ?></td>
                                <td>
                                    <?php if ($ticket->ticketStatus !== null) : ?>
                                        <span class="badge <?= Html::encode($ticket->ticketStatus->getColorBadgeClass()) ?>">
                                            <?= Html::encode($ticket->ticketStatus->name) ?>
                                        </span>
                                    <?php else : ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?= Html::encode($ticket->getPriorityBadgeClass()) ?>">
                                        <?= Html::encode($ticket->getPriorityLabel()) ?>
                                    </span>
                                </td>
                                <td class="text-nowrap"><?= Html::encode($ticket->getFormattedCreatedAt()) ?></td>
                                <td class="text-end">
                                    <?= Html::a('<i class="bi bi-eye"></i>', ['view', 'id' => $ticket->id], [
                                        'class' => 'btn btn-sm btn-outline-secondary',
                                        'title' => 'Открыть',
                                    ]) ?>
                                    <?php if ($canProcess) : ?>
                                        <?= Html::a('<i class="bi bi-pencil"></i>', ['update', 'id' => $ticket->id], [
                                            'class' => 'btn btn-sm btn-outline-primary',
                                            'title' => 'Редактировать',
                                        ]) ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php if ($dataProvider->pagination !== false && $dataProvider->pagination->pageCount > 1) : ?>
                <div class="card-footer d-flex justify-content-center">
                    <?= LinkPager::widget([
                        'pagination' => $dataProvider->pagination,
                        'options' => ['class' => 'pagination mb-0'],
                        'linkOptions' => ['class' => 'page-link'],
                        'pageCssClass' => 'page-item',
                        'activePageCssClass' => 'page-item active',
                        'disabledPageCssClass' => 'page-item disabled',
                        'prevPageCssClass' => 'page-item',
                        'nextPageCssClass' => 'page-item',
                        'firstPageCssClass' => 'page-item',
                        'lastPageCssClass' => 'page-item',
                    ]) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<!--end::App Content-->
