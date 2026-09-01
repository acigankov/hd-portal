<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use Yii;

/* @var $this yii\web\View */
/* @var $model app\models\User */
/* @var $form yii\widgets\ActiveForm */

$this->title = Yii::t('yii2mod.rbac', 'Создание пользователя');
$this->params['breadcrumbs'][] = $this->title;

// Получаем список ролей из RBAC
$roles = [];
foreach (Yii::$app->authManager->getRoles() as $name => $role) {
    $roles[$name] = $role->description ?: $name;
}
?>


<!--begin::App Content Header-->
<div class="app-content-header">
    <!--begin::Container-->
    <div class="container-fluid">
        <!--begin::Row-->
        <div class="row">
            <div class="col-sm-6"><h3 class="mb-0"><?php echo Html::encode($this->title); ?></h3></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo Html::encode($this->title); ?></li>
                </ol>
            </div>
        </div>
        <!--end::Row-->
    </div>
    <!--end::Container-->
</div>
<!--end::App Content Header-->


    <!--begin::App Content -->
<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-6">
                <div class="user-form">
                    <div class="card">
                        <div class="card-body">
                            <?php $form = ActiveForm::begin([
                                'id' => 'user-create-form',
                                'errorCssClass' => 'invalid-feedback', // ваш кастомный класс
                                'fieldConfig' => [
                                    'template' => "{label}\n<div class=\"col\">{input}</div>\n<div class=\"col\">{error}</div>",
                                    'labelOptions' => ['class' => 'col-sm-2 col-form-label'],
                                ],
                            ]); ?>

                            <?= $form->field($model, 'login')
                                ->textInput(['autofocus' => true, 'placeholder' => 'Введите имя пользователя'])
                                ->label('Имя пользователя *') ?>

                            <?= $form->field($model, 'name')
                                ->textInput(['placeholder' => 'Введите имя'])
                                ->label('Имя *') ?>

                            <?= $form->field($model, 'email')
                                ->textInput(['type' => 'email', 'placeholder' => 'user@example.com'])
                                ->label('Email *') ?>

                            <?= $form->field($model, 'role')
                                ->dropDownList($roles, ['prompt' => 'Выберите роль'])
                                ->label('Роль *') ?>

                            <?= $form->field($model, 'password')
                                ->passwordInput(['placeholder' => 'Минимум 6 символов'])
                                ->label('Пароль *') ?>

                            <div class="form-group">
                                <?= Html::submitButton('<i class="fas fa-save"></i> Создать пользователя', [
                                    'class' => 'btn btn-primary'
                                ]) ?>
                                <?= Html::a('Отмена', ['index'], ['class' => 'btn btn-secondary']) ?>
                            </div>

                            <?php ActiveForm::end(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


    <!--end::App Content -->

