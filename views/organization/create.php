<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Organization $model */

$this->title = 'Создать организацию';
$this->params['breadcrumbs'][] = ['label' => 'Организации', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="organization-create">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1><?= Html::encode($this->title) ?></h1>
    </div>

    <div class="card">
        <div class="card-body">
            <?= $this->render('_form', [
                'model' => $model,
            ]) ?>
        </div>
    </div>

</div>
