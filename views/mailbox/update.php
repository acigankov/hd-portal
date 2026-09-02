<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Mailbox */

$this->title = 'Настройка канала: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Почтовые каналы', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Настройка';
?>

<div class="app-content-header">
    <div class="container-fluid">
        <h3 class="mb-0"><?= Html::encode($this->title) ?></h3>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <?= $this->render('_form', ['model' => $model]) ?>
    </div>
</div>
