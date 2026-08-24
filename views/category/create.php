<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Category */
/* @var $form yii\widgets\ActiveForm */

$this->title = Yii::t('app', 'Create category');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Categories'), 'url' => ['index']];
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
                    <li class="breadcrumb-item"><a href="<?= \yii\helpers\Url::to(['index']) ?>"><?= Yii::t('app', 'Categories') ?></a></li>
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
                <div class="category-form">
                    <div class="card">
                        <div class="card-body">
                            <?php $form = ActiveForm::begin([
                                'id' => 'category-create-form',
                                'errorCssClass' => 'invalid-feedback',
                                'fieldConfig' => [
                                    'template' => "{label}\n<div class=\"col\">{input}</div>\n<div class=\"col\">{error}</div>",
                                    'labelOptions' => ['class' => 'col-sm-3 col-form-label'],
                                ],
                            ]); ?>

                            <?= $form->field($model, 'name')
                                ->textInput(['autofocus' => true, 'placeholder' => Yii::t('app', 'Enter category name'), 'id' => 'category-name'])
                                ->label(Yii::t('app', 'Name') . ' *') ?>

                            <?= $form->field($model, 'code')
                                ->textInput(['maxlength' => 50, 'placeholder' => 'e.g. hardware, software, network', 'id' => 'category-code'])
                                ->label(Yii::t('app', 'Code') . ' *') ?>

                            <?= $form->field($model, 'description')
                                ->textarea(['rows' => 3, 'placeholder' => Yii::t('app', 'Enter description')])
                                ->label(Yii::t('app', 'Description')) ?>

                            <?= $form->field($model, 'color')
                                ->dropDownList([
                                    'primary' => Yii::t('app', 'Primary'),
                                    'secondary' => Yii::t('app', 'Secondary'),
                                    'success' => Yii::t('app', 'Success'),
                                    'danger' => Yii::t('app', 'Danger'),
                                    'warning' => Yii::t('app', 'Warning'),
                                    'info' => Yii::t('app', 'Info'),
                                    'light' => Yii::t('app', 'Light'),
                                    'dark' => Yii::t('app', 'Dark'),
                                ])
                                ->label(Yii::t('app', 'Color')) ?>

                            <?= $form->field($model, 'icon')
                                ->textInput(['maxlength' => 50, 'placeholder' => 'e.g. folder, laptop, wifi'])
                                ->label(Yii::t('app', 'Icon')) ?>

                            <?= $form->field($model, 'sort_order')
                                ->textInput(['type' => 'number', 'placeholder' => '0'])
                                ->label(Yii::t('app', 'Sort Order')) ?>

                            <hr>
                            
                            <div class="mb-3">
                                <label class="col-sm-3 col-form-label"><?= Yii::t('app', 'Entity Types') ?></label>
                                <div class="col">
                                    <div class="form-check form-check-inline">
                                        <?= $form->field($model, 'for_requests')->checkbox(['label' => Yii::t('app', 'Requests'), 'class' => 'form-check-input']) ?>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <?= $form->field($model, 'for_tasks')->checkbox(['label' => Yii::t('app', 'Tasks'), 'class' => 'form-check-input']) ?>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <?= $form->field($model, 'for_problems')->checkbox(['label' => Yii::t('app', 'Problems'), 'class' => 'form-check-input']) ?>
                                    </div>
                                </div>
                            </div>

                            <?= $form->field($model, 'status')
                                ->dropDownList([
                                    \app\models\Category::STATUS_ACTIVE => Yii::t('app', 'Active'),
                                    \app\models\Category::STATUS_INACTIVE => Yii::t('app', 'Inactive'),
                                ])
                                ->label(Yii::t('app', 'Status')) ?>

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

<?php
$translitUrl = \yii\helpers\Url::to(['transliterate']);
$this->registerJs(<<<JS
(function() {
    const nameInput = document.getElementById('category-name');
    const codeInput = document.getElementById('category-code');
    
    if (!nameInput || !codeInput) return;
    
    let manualEdit = false;
    
    codeInput.addEventListener('focus', function() {
        if (this.value !== '') {
            manualEdit = true;
        }
    });
    
    codeInput.addEventListener('input', function() {
        manualEdit = true;
    });
    
    nameInput.addEventListener('input', function() {
        if (!manualEdit && this.value.trim() !== '') {
            fetch('$translitUrl', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ text: this.value })
            })
            .then(response => response.json())
            .then(data => {
                if (data.transliteration) {
                    codeInput.value = data.transliteration.toLowerCase().replace(/[^a-z0-9_]/g, '_');
                    manualEdit = false;
                }
            })
            .catch(err => console.error('Transliteration error:', err));
        }
    });
})();
JS, \yii\web\View::POS_END);
?>


<!--end::App Content -->
