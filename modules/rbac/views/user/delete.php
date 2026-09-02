<?php

use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $model \app\models\User */
?>
<div class="user-delete">
    <h1>Удалить пользователя?</h1>
    <p>Вы уверены, что хотите удалить "<?= Html::encode($model->login) ?>"?</p>

    <?= Html::beginForm(['delete', 'id' => $model->id], 'post') ?>
        <?= Html::submitButton('Удалить', ['class' => 'btn btn-danger']) ?>
        <?= Html::a('Отмена', ['index'], ['class' => 'btn btn-default']) ?>
    <?= Html::endForm() ?>
</div>