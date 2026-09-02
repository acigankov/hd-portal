<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\Ticket */
/* @var $mailboxes app\models\Mailbox[] */
/* @var $organizations app\models\Organization[] */
/* @var $authors app\models\Author[] */
/* @var $categories app\models\Category[] */
/* @var $statuses app\models\Status[] */
/* @var $users app\models\User[] */

$this->title = 'Заявка ' . $model->ticket_number;
?>

<!--begin::App Content Header-->
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6"><h3 class="mb-0">Редактирование заявки <?= Html::encode($model->ticket_number) ?></h3></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="<?= Url::to(['/site/index']) ?>">Главная</a></li>
                    <li class="breadcrumb-item"><a href="<?= Url::to(['index']) ?>">Заявки</a></li>
                    <li class="breadcrumb-item"><a href="<?= Url::to(['view', 'id' => $model->id]) ?>"><?= Html::encode($model->ticket_number) ?></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Редактирование</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<!--end::App Content Header-->

<!--begin::App Content-->
<div class="app-content">
    <div class="container-fluid">
        <?= $this->render('_form', [
            'model' => $model,
            'mailboxes' => $mailboxes,
            'organizations' => $organizations,
            'authors' => $authors,
            'categories' => $categories,
            'statuses' => $statuses,
            'users' => $users,
        ]) ?>
    </div>
</div>
<!--end::App Content-->
