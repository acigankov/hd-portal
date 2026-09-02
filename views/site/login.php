<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */

/** @var app\models\LoginForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = Yii::t('app', 'Login');
$this->params['breadcrumbs'][] = $this->title;
?>


<div class="login-box">
    <div class="login-logo">
        <a href="/"><?php echo \yii\helpers\Html::encode(YII::$app->params['appName'])?></a>
    </div>
    <!-- /.login-logo -->
    <div class="card">
        <div class="card-body login-card-body">
            <p class="login-box-msg"><?= Yii::t('app', 'Please sign in') ?></p>

            <?php $form = ActiveForm::begin([
                'id' => 'login-form',
            ]); ?>

            <?= $form->field($model, 'login')->textInput(['placeholder' => Yii::t('app', 'Enter login:')])->label(Yii::t('app', 'Enter login:')) ?>

            <?= $form->field($model, 'password')->passwordInput(['placeholder' => Yii::t('app', 'Enter password:')])->label(Yii::t('app', 'Enter password:'))  ?>

            <!--begin::Row-->
            <div class="row align-items-center">
                <div class="col-8">
                    <?= $form->field($model, 'rememberMe', [
                            'options' => [
                                'tag' => false, // убирает обёртку
                            ]
                        ]
                    )->checkbox(['label' => Yii::t('app', 'Remember me')]) ?>


                </div>
                <!-- /.col -->
                <div class="col-4">
                    <div class="d-grid gap-2">
                        <?= Html::submitButton(Yii::t('app', 'Sign In'), ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
                    </div>
                </div>
                <!-- /.col -->
            </div>
            <!--end::Row-->
            <?php ActiveForm::end(); ?>
            <!-- /.social-auth-links -->
            <p class="mb-1"><a href=""><?= Yii::t('app', 'I forgot my password') ?> </a></p>

        </div>
        <!-- /.login-card-body -->
    </div>
</div>
<!-- /.login-box -->