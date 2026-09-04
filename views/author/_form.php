<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Organization;

/* @var $this yii\web\View */
/* @var $model app\models\Author */
/* @var $form yii\widgets\ActiveForm */

$organizations = Organization::find()->orderBy(['name' => SORT_ASC])->all();
?>

<div class="author-form">

    <!--begin::App Content Header-->
    <div class="app-content-header">
        <!--begin::Container-->
        <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
                <div class="col-sm-6"><h3 class="mb-0"><?php echo Html::encode($this->title); ?></h3></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="/"><?= Yii::t('app', 'Home') ?></a></li>
                        <li class="breadcrumb-item"><a href="/author"><?= Yii::t('app', 'Authors') ?></a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo Html::encode($this->title); ?></li>
                    </ol>
                </div>
            </div>
            <!--end::Row-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::App Content Header-->
    
    <!--begin::App Content-->
    <div class="app-content">
        <!--begin::Container-->
        <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
                <!--begin::Col-->
                <div class="col">
                    <div class="card">
                        <div class="card-body">
                            <?php $form = ActiveForm::begin(); ?>

                            <?= $form->field($model, 'full_name')->textInput(['maxlength' => true, 'placeholder' => Yii::t('app', 'Enter full name')]) ?>

                            <?= $form->field($model, 'avatar')->textInput(['maxlength' => true, 'placeholder' => Yii::t('app', 'Enter avatar path')]) ?>

                            <?= $form->field($model, 'email')->textInput(['maxlength' => true, 'placeholder' => Yii::t('app', 'Enter email')]) ?>

                            <?= $form->field($model, 'phone')->textInput(['maxlength' => true, 'placeholder' => Yii::t('app', 'Enter phone')]) ?>

                            <?= $form->field($model, 'position')->textInput(['maxlength' => true, 'placeholder' => Yii::t('app', 'Enter position')]) ?>

                            <?= $form->field($model, 'organization_id')->dropDownList(
                                \yii\helpers\ArrayHelper::map($organizations, 'id', 'name'),
                                ['prompt' => Yii::t('app', 'Select organization'), 'class' => 'form-control']
                            ) ?>

                            <?= $form->field($model, 'status')->dropDownList([
                                1 => Yii::t('app', 'Active'),
                                0 => Yii::t('app', 'Inactive'),
                            ], ['class' => 'form-control']) ?>

                            <div class="form-group">
                                <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
                                <?= Html::a(Yii::t('app', 'Back to list'), ['/author/index'], ['class' => 'btn btn-default']) ?>
                            </div>

                            <?php ActiveForm::end(); ?>
                        </div>
                    </div>
                </div>
                <!--end::Col-->
            </div>
            <!--end::Row-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::App Content-->

</div>
