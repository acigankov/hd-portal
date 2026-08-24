<?php

use app\models\Category;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Category */
/* @var $form yii\widgets\ActiveForm */

$this->title = Yii::t('app', 'Edit category') . ': ' . Html::encode($model->name);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Categories'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => Html::encode($model->name), 'url' => ['view', 'id' => $model->id]];
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
                    <li class="breadcrumb-item"><a href="<?= \yii\helpers\Url::to(['index']) ?>"><?= Yii::t('app', 'Categories') ?></a></li>
                    <li class="breadcrumb-item"><a href="<?= \yii\helpers\Url::to(['view', 'id' => $model->id]) ?>"><?= Html::encode($model->name) ?></a></li>
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
                                'id' => 'category-update-form',
                                'errorCssClass' => 'invalid-feedback',
                                'fieldConfig' => [
                                    'template' => "{label}\n<div class=\"col\">{input}</div>\n<div class=\"col\">{error}</div>",
                                    'labelOptions' => ['class' => 'col-sm-3 col-form-label'],
                                ],
                            ]); ?>

                            <?= $form->field($model, 'name')
                                ->textInput(['maxlength' => 255, 'placeholder' => Yii::t('app', 'Enter category name'), 'id' => 'category-name'])
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
                                ->dropDownList([
                                    'bi-folder' => Yii::t('app', 'Folder'),
                                    'bi-folder-open' => Yii::t('app', 'Folder Open'),
                                    'bi-file-text' => Yii::t('app', 'File Text'),
                                    'bi-file-earmark' => Yii::t('app', 'File'),
                                    'bi-card-list' => Yii::t('app', 'Card List'),
                                    'bi-card-text' => Yii::t('app', 'Card Text'),
                                    'bi-laptop' => Yii::t('app', 'Laptop'),
                                    'bi-pc-display' => Yii::t('app', 'PC Display'),
                                    'bi-monitor' => Yii::t('app', 'Monitor'),
                                    'bi-keyboard' => Yii::t('app', 'Keyboard'),
                                    'bi-mouse' => Yii::t('app', 'Mouse'),
                                    'bi-hdd' => Yii::t('app', 'HDD'),
                                    'bi-cpu' => Yii::t('app', 'CPU'),
                                    'bi-memory' => Yii::t('app', 'Memory'),
                                    'bi-wifi' => Yii::t('app', 'WiFi'),
                                    'bi-router' => Yii::t('app', 'Router'),
                                    'bi-ethernet' => Yii::t('app', 'Ethernet'),
                                    'bi-printer' => Yii::t('app', 'Printer'),
                                    'bi-camera' => Yii::t('app', 'Camera'),
                                    'bi-phone' => Yii::t('app', 'Phone'),
                                    'bi-tablet' => Yii::t('app', 'Tablet'),
                                    'bi-headset' => Yii::t('app', 'Headset'),
                                    'bi-speaker' => Yii::t('app', 'Speaker'),
                                    'bi-mic' => Yii::t('app', 'Microphone'),
                                    'bi-display' => Yii::t('app', 'Display'),
                                    'bi-tv' => Yii::t('app', 'TV'),
                                    'bi-projector' => Yii::t('app', 'Projector'),
                                    'bi-hard-drive' => Yii::t('app', 'Hard Drive'),
                                    'bi-usb-drive' => Yii::t('app', 'USB Drive'),
                                    'bi-disc' => Yii::t('app', 'Disc'),
                                    'bi-cloud' => Yii::t('app', 'Cloud'),
                                    'bi-database' => Yii::t('app', 'Database'),
                                    'bi-server' => Yii::t('app', 'Server'),
                                    'bi-globe' => Yii::t('app', 'Globe'),
                                    'bi-link' => Yii::t('app', 'Link'),
                                    'bi-broadcast' => Yii::t('app', 'Broadcast'),
                                    'bi-signal' => Yii::t('app', 'Signal'),
                                    'bi-lightning' => Yii::t('app', 'Lightning'),
                                    'bi-lightbulb' => Yii::t('app', 'Lightbulb'),
                                    'bi-power' => Yii::t('app', 'Power'),
                                    'bi-toggle-on' => Yii::t('app', 'Toggle On'),
                                    'bi-toggle-off' => Yii::t('app', 'Toggle Off'),
                                    'bi-gear' => Yii::t('app', 'Gear'),
                                    'bi-tools' => Yii::t('app', 'Tools'),
                                    'bi-wrench' => Yii::t('app', 'Wrench'),
                                    'bi-screwdriver' => Yii::t('app', 'Screwdriver'),
                                    'bi-pencil' => Yii::t('app', 'Pencil'),
                                    'bi-book' => Yii::t('app', 'Book'),
                                    'bi-journal' => Yii::t('app', 'Journal'),
                                    'bi-notebook' => Yii::t('app', 'Notebook'),
                                    'bi-collection' => Yii::t('app', 'Collection'),
                                    'bi-box' => Yii::t('app', 'Box'),
                                    'bi-archive' => Yii::t('app', 'Archive'),
                                    'bi-tag' => Yii::t('app', 'Tag'),
                                    'bi-tags' => Yii::t('app', 'Tags'),
                                    'bi-bookmark' => Yii::t('app', 'Bookmark'),
                                    'bi-flag' => Yii::t('app', 'Flag'),
                                    'bi-star' => Yii::t('app', 'Star'),
                                    'bi-heart' => Yii::t('app', 'Heart'),
                                    'bi-shield' => Yii::t('app', 'Shield'),
                                    'bi-lock' => Yii::t('app', 'Lock'),
                                    'bi-unlock' => Yii::t('app', 'Unlock'),
                                    'bi-key' => Yii::t('app', 'Key'),
                                    'bi-person' => Yii::t('app', 'Person'),
                                    'bi-people' => Yii::t('app', 'People'),
                                    'bi-chat' => Yii::t('app', 'Chat'),
                                    'bi-envelope' => Yii::t('app', 'Envelope'),
                                    'bi-inbox' => Yii::t('app', 'Inbox'),
                                    'bi-bell' => Yii::t('app', 'Bell'),
                                    'bi-alarm' => Yii::t('app', 'Alarm'),
                                    'bi-clock' => Yii::t('app', 'Clock'),
                                    'bi-calendar' => Yii::t('app', 'Calendar'),
                                    'bi-stopwatch' => Yii::t('app', 'Stopwatch'),
                                    'bi-graph-up' => Yii::t('app', 'Graph Up'),
                                    'bi-pie-chart' => Yii::t('app', 'Pie Chart'),
                                    'bi-bar-chart' => Yii::t('app', 'Bar Chart'),
                                    'bi-activity' => Yii::t('app', 'Activity'),
                                    'bi-speedometer' => Yii::t('app', 'Speedometer'),
                                    'bi-check' => Yii::t('app', 'Check'),
                                    'bi-check-circle' => Yii::t('app', 'Check Circle'),
                                    'bi-x' => Yii::t('app', 'X'),
                                    'bi-x-circle' => Yii::t('app', 'X Circle'),
                                    'bi-plus' => Yii::t('app', 'Plus'),
                                    'bi-plus-circle' => Yii::t('app', 'Plus Circle'),
                                    'bi-dash' => Yii::t('app', 'Dash'),
                                    'bi-info' => Yii::t('app', 'Info'),
                                    'bi-info-circle' => Yii::t('app', 'Info Circle'),
                                    'bi-exclamation-triangle' => Yii::t('app', 'Warning'),
                                    'bi-question-circle' => Yii::t('app', 'Question'),
                                ], ['prompt' => Yii::t('app', 'Select icon...')])
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
                                    Category::STATUS_ACTIVE => Yii::t('app', 'Active'),
                                    Category::STATUS_INACTIVE => Yii::t('app', 'Inactive'),
                                ])
                                ->label(Yii::t('app', 'Status')) ?>

                            <div class="form-group">
                                <?= Html::submitButton('<i class="fas fa-save"></i> ' . Yii::t('app', 'Save'), [
                                    'class' => 'btn btn-primary'
                                ]) ?>
                                <?= Html::a(Yii::t('app', 'Cancel'), ['view', 'id' => $model->id], ['class' => 'btn btn-secondary']) ?>
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
