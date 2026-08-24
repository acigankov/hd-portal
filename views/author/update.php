<?php

/* @var $this yii\web\View */
/* @var $model app\models\Author */

$this->title = Yii::t('app', 'Edit author: ') . $model->full_name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Authors'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->full_name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Edit');
?>

<div class="author-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>
