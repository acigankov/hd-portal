<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Organization */
/* @var $form yii\widgets\ActiveForm */

$this->title = Yii::t('app', 'Create organization');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Organizations'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
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
                    <li class="breadcrumb-item"><a href="#"><?= Yii::t('app', 'Home') ?></a></li>
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
                <div class="organization-form">
                    <div class="card">
                        <div class="card-body">
                            <?php $form = ActiveForm::begin([
                                'id' => 'organization-create-form',
                                'errorCssClass' => 'invalid-feedback',
                                'fieldConfig' => [
                                    'template' => "{label}\n<div class=\"col\">{input}</div>\n<div class=\"col\">{error}</div>",
                                    'labelOptions' => ['class' => 'col-sm-2 col-form-label'],
                                ],
                            ]); ?>

                            <?= $form->field($model, 'name')
                                ->textInput(['autofocus' => true, 'placeholder' => Yii::t('app', 'Enter organization name')])
                                ->label(Yii::t('app', 'Name') . ' *') ?>

                            <?= $form->field($model, 'inn')
                                ->textInput(['maxlength' => 12, 'placeholder' => '123456789012'])
                                ->label(Yii::t('app', 'INN') . ' *') ?>

                            <?= $form->field($model, 'kpp')
                                ->textInput(['maxlength' => 9, 'placeholder' => '123456789'])
                                ->label(Yii::t('app', 'KPP')) ?>

                            <?= $form->field($model, 'ogrn')
                                ->textInput(['maxlength' => 15, 'placeholder' => '1234567890123'])
                                ->label(Yii::t('app', 'OGRN')) ?>

                            <?= $form->field($model, 'director_name')
                                ->textInput(['maxlength' => true, 'placeholder' => Yii::t('app', 'Enter director name')])
                                ->label(Yii::t('app', 'Director')) ?>

                            <?= $form->field($model, 'phone')
                                ->textInput(['maxlength' => 20, 'placeholder' => '+7 (XXX) XXX-XX-XX'])
                                ->label(Yii::t('app', 'Phone')) ?>

                            <?= $form->field($model, 'email')
                                ->textInput(['type' => 'email', 'placeholder' => 'info@example.com'])
                                ->label(Yii::t('app', 'Email')) ?>

                            <?= $form->field($model, 'website')
                                ->textInput(['maxlength' => true, 'placeholder' => 'https://example.com'])
                                ->label(Yii::t('app', 'Website')) ?>

                            <?= $form->field($model, 'legal_address')
                                ->textarea(['rows' => 3, 'placeholder' => Yii::t('app', 'Legal address')])
                                ->label(Yii::t('app', 'Legal address')) ?>

                            <?= $form->field($model, 'actual_address')
                                ->textarea(['rows' => 3, 'placeholder' => Yii::t('app', 'Actual address')])
                                ->label(Yii::t('app', 'Actual address')) ?>

                            <?= $form->field($model, 'status')
                                ->dropDownList([
                                    \app\models\Organization::STATUS_ACTIVE => Yii::t('app', 'Active'),
                                    \app\models\Organization::STATUS_INACTIVE => Yii::t('app', 'Inactive'),
                                ])
                                ->label(Yii::t('app', 'Status')) ?>

                            <div class="form-group">
                                <?= Html::submitButton('<i class="fas fa-save"></i> ' . Yii::t('app', 'Create organization'), [
                                    'class' => 'btn btn-primary'
                                ]) ?>
                                <?= Html::a(Yii::t('app', 'Cancel'), ['index'], ['class' => 'btn btn-secondary']) ?>
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
