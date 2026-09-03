<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Task */
/* @var $form yii\widgets\ActiveForm */
/* @var $categories array */
/* @var $statuses array */
/* @var $authors array */
/* @var $users array */

$this->title = Yii::t('app', 'Create task');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Tasks'), 'url' => ['index']];
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
                    <li class="breadcrumb-item"><a href="<?= \yii\helpers\Url::to(['index']) ?>"><?= Yii::t('app', 'Tasks') ?></a></li>
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
            <div class="col-8">
                <div class="task-form">
                    <div class="card">
                        <div class="card-body">
                            <?php $form = ActiveForm::begin([
                                'id' => 'task-create-form',
                                'errorCssClass' => 'invalid-feedback',
                                'fieldConfig' => [
                                    'template' => "{label}\n<div class=\"col\">{input}</div>\n<div class=\"col\">{error}</div>",
                                    'labelOptions' => ['class' => 'col-sm-3 col-form-label'],
                                ],
                            ]); ?>

                            <?= $form->field($model, 'title')
                                ->textInput(['maxlength' => 255, 'autofocus' => true, 'placeholder' => Yii::t('app', 'Enter task title')])
                                ->label(Yii::t('app', 'Title') . ' *') ?>

                            <?= $form->field($model, 'description')
                                ->textarea(['rows' => 4, 'placeholder' => Yii::t('app', 'Enter description'), 'class' => 'form-control quill-editor'])
                                ->label(Yii::t('app', 'Description')) ?>

                            <?= $form->field($model, 'category_id')
                                ->dropDownList(
                                    \yii\helpers\ArrayHelper::map($categories, 'id', 'name'),
                                    ['prompt' => Yii::t('app', 'Select category')]
                                )
                                ->label(Yii::t('app', 'Category')) ?>

                            <?= $form->field($model, 'status_id')
                                ->dropDownList(
                                    \yii\helpers\ArrayHelper::map($statuses, 'id', 'name'),
                                    ['prompt' => Yii::t('app', 'Select status')]
                                )
                                ->label(Yii::t('app', 'Status')) ?>

                            <?= $form->field($model, 'author_id')
                                ->dropDownList(
                                    \yii\helpers\ArrayHelper::map($authors, 'id', 'fullName'),
                                    ['prompt' => Yii::t('app', 'Select author')]
                                )
                                ->label(Yii::t('app', 'Author')) ?>

                            <?= $form->field($model, 'responsible_id')
                                ->dropDownList(
                                    \yii\helpers\ArrayHelper::map($users, 'id', 'login'),
                                    ['prompt' => Yii::t('app', 'Select responsible')]
                                )
                                ->label(Yii::t('app', 'Responsible') . ' *') ?>

                            <?= $form->field($model, 'priority')
                                ->dropDownList([
                                    \app\models\Task::PRIORITY_LOW => Yii::t('app', 'Low'),
                                    \app\models\Task::PRIORITY_MEDIUM => Yii::t('app', 'Medium'),
                                    \app\models\Task::PRIORITY_HIGH => Yii::t('app', 'High'),
                                ])
                                ->label(Yii::t('app', 'Priority')) ?>

                            <?= $form->field($model, 'due_date')
                                ->input('date', ['format' => 'yyyy-MM-dd'])
                                ->label(Yii::t('app', 'Due Date')) ?>


                            <div class="form-group">
                                <?= Html::submitButton('<i class="fas fa-save"></i> ' . Yii::t('app', 'Save'), [
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
