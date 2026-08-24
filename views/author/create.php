<?php

/* @var $this yii\web\View */
/* @var $model app\models\Author */

$this->title = Yii::t('app', 'Create author');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Authors'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="author-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>
