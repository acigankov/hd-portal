<?php

use app\models\Problem;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Problem */
/* @var $form yii\widgets\ActiveForm */
/* @var $categories array */
/* @var $statuses array */
/* @var $authors array */
/* @var $users array */
/* @var $tickets array */

$this->title = Yii::t('app', 'Edit problem') . ': ' . Html::encode($model->problem_number);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Problems'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => Html::encode($model->problem_number), 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Edit');
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
                    <li class="breadcrumb-item"><a
                                href="<?= Url::to(['index']) ?>"><?= Yii::t('app', 'Problems') ?></a></li>
                    <li class="breadcrumb-item"><a
                                href="<?= Url::to(['view', 'id' => $model->id]) ?>"><?= Html::encode($model->problem_number) ?></a>
                    </li>
                    <li class="breadcrumb-item active"
                        aria-current="page"><?php echo Html::encode($this->title); ?></li>
                </ol>
            </div>
        </div>
        <!--end::Row-->
    </div>
    <!--end::Container-->
</div>
<!--end::App Content Header--><!--begin::App Content -->
<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-8">
                <div class="problem-form">
                    <div class="card">
                        <div class="card-body">
                            <?php $form = ActiveForm::begin([
                                    'id' => 'problem-update-form',
                                    'errorCssClass' => 'invalid-feedback',
                                    'fieldConfig' => ['template' => "{label}\<div class=\"col\">{input}</div>\<div class=\"col\">{error}</div>",
                                    'labelOptions' => ['class' => 'col-sm-3 col-form-label'],
                                        ],
                                ]) ?>

                            <?= $form->field($model, 'title')
                                ->textInput(['maxlength' => 255, 'autofocus' => true, 'placeholder' => Yii::t('app', 'Enter problem title')])
                                ->label(Yii::t('app', 'Title') . ' *') ?>

                            <?= $form->field($model, 'description')
                                ->textarea(['rows' => 4, 'placeholder' => Yii::t('app', 'Enter description')])
                                ->label(Yii::t('app', 'Description')) ?>

                            <?= $form->field($model, 'jira_ticket')
                                ->textInput(['maxlength' => 255, 'placeholder' => Yii::t('app', 'Enter Jira ticket URL')])
                                ->label(Yii::t('app', 'Jira Ticket')) ?>

                            <?= $form->field($model, 'category_id')
                                ->dropDownList(
                                    ArrayHelper::map($categories, 'id', 'name'),
                                    ['prompt' => Yii::t('app', 'Select category')]
                                )
                                ->label(Yii::t('app', 'Category')) ?>

                            <?= $form->field($model, 'status_id')
                                ->dropDownList(
                                    ArrayHelper::map($statuses, 'id', 'name'),
                                    ['prompt' => Yii::t('app', 'Select status')]
                                )
                                ->label(Yii::t('app', 'Status')) ?>

                            <?= $form->field($model, 'author_id')
                                ->dropDownList(
                                    ArrayHelper::map($authors, 'id', 'fullName'),
                                    ['prompt' => Yii::t('app', 'Select author')]
                                )
                                ->label(Yii::t('app', 'Author')) ?>

                            <?= $form->field($model, 'responsible_id')
                                ->dropDownList(
                                    ArrayHelper::map($users, 'id', 'login'),
                                    ['prompt' => Yii::t('app', 'Select responsible')]
                                )
                                ->label(Yii::t('app', 'Responsible') . ' *') ?>

                            <?= $form->field($model, 'priority')
                                ->dropDownList([Problem::PRIORITY_LOW => Yii::t('app', 'Low'),
                                    Problem::PRIORITY_MEDIUM => Yii::t('app', 'Medium'),
                                    Problem::PRIORITY_HIGH => Yii::t('app', 'High'),
                                    ])
                                ->label(Yii::t('app', 'Priority')) ?>

                            <?= $form->field($model, 'due_date')
                                ->input('date', ['format' => 'yyyy-MM-dd'])
                                ->label(Yii::t('app', 'Due Date')) ?>

                            <?= $form->field($model, 'ticket_ids')
                                ->listBox(
                                    ArrayHelper::map($tickets, 'id', 'ticket_number'),
                                    ['multiple' => true, 'size' => 10]
                                )
                                ->label(Yii::t('app', 'Related Tickets')) ?>


                            <div class="form-group">
                                <?= Html::submitButton('<i class="fas fa-save"></i> ' . Yii::t('app', 'Save'),
                                    [
                                            'class' => 'btbtn-primary'
                                    ])
                                ?>
                                <?= Html::a(Yii::t('app', 'Cancel'), ['index'], ['class' => 'btbtn-secondary']) ?>
                                
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
