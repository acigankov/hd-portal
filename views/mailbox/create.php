<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Mailbox */

$this->title = 'Новый почтовый канал';
$this->params['breadcrumbs'][] = ['label' => 'Почтовые каналы', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
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
