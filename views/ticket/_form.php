<?php

use app\models\Ticket;
use app\models\TicketReply;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Ticket */
/* @var $mailboxes app\models\Mailbox[] */
/* @var $organizations app\models\Organization[] */
/* @var $authors app\models\Author[] */
/* @var $categories app\models\Category[] */
/* @var $statuses app\models\Status[] */
/* @var $users app\models\User[] */

$form = ActiveForm::begin([
    'id' => 'ticket-form',
    'options' => ['class' => 'row g-3'],
    'fieldConfig' => [
        'options' => ['class' => 'mb-3'],
        'labelOptions' => ['class' => 'form-label'],
    ],
]);
?>

<div class="col-lg-8">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title mb-0">Обращение</h3>
        </div>
        <div class="card-body">
            <?= $form->field($model, 'subject')
                ->textInput(['maxlength' => 255, 'autofocus' => $model->isNewRecord, 'placeholder' => 'Коротко о проблеме'])
                ->label('Тема *') ?>

            <?= $form->field($model, 'description')
                ->textarea(['rows' => 8, 'placeholder' => 'Что произошло, что уже проверили, как воспроизвести'])
                ->label('Описание') ?>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h3 class="card-title mb-0">Автор обращения</h3>
        </div>
        <div class="card-body">
            <p class="text-muted small">
                Если заявитель есть в справочнике — выберите его, пустые поля ниже заполнятся автоматически.
                Если нет — достаточно указать ФИО и контакты вручную.
            </p>

            <?= $form->field($model, 'author_id')
                ->dropDownList(
                    ArrayHelper::map($authors, 'id', 'full_name'),
                    ['prompt' => 'Нет в справочнике']
                )
                ->label('Заявитель из справочника') ?>

            <?= $form->field($model, 'author_name')
                ->textInput(['maxlength' => 255, 'placeholder' => 'Иванов Иван Иванович'])
                ->label('ФИО автора *') ?>

            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'author_email')
                        ->textInput(['maxlength' => 255, 'placeholder' => 'user@example.com'])
                        ->label('Email') ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'author_phone')
                        ->textInput(['maxlength' => 50, 'placeholder' => '+7 900 000-00-00'])
                        ->label('Телефон') ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-lg-4">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title mb-0">Обработка</h3>
        </div>
        <div class="card-body">
            <?= $form->field($model, 'organization_id')
                ->dropDownList(
                    ArrayHelper::map($organizations, 'id', 'name'),
                    ['prompt' => 'Не указана']
                )
                ->label('Организация') ?>

            <?= $form->field($model, 'category_id')
                ->dropDownList(
                    ArrayHelper::map($categories, 'id', 'name'),
                    ['prompt' => 'Без категории']
                )
                ->label('Категория') ?>

            <?php if (!empty($mailboxes)) : ?>
                <?= $form->field($model, 'mailbox_id')
                    ->dropDownList(
                        ArrayHelper::map($mailboxes, 'id', static function ($mailbox) {
                            return $mailbox->name . ' (' . $mailbox->email . ')';
                        }),
                        ['prompt' => 'Не связана с почтовым каналом']
                    )
                    ->label('Почтовый канал')
                    ->hint('Ответы заявителю уйдут с адреса этого ящика.') ?>
            <?php endif; ?>

            <?= $form->field($model, 'assigned_id')
                ->dropDownList(
                    ArrayHelper::map($users, 'id', static function ($user) {
                        return TicketReply::userName($user);
                    }),
                    ['prompt' => 'Не назначен']
                )
                ->label('Назначенный специалист') ?>

            <?= $form->field($model, 'status_id')
                ->dropDownList(
                    ArrayHelper::map($statuses, 'id', 'name'),
                    ['prompt' => $model->isNewRecord ? 'Статус по умолчанию' : 'Не указан']
                )
                ->label('Статус') ?>

            <?= $form->field($model, 'priority')
                ->dropDownList(Ticket::priorityList())
                ->label('Приоритет') ?>

            <div class="d-flex gap-2">
                <?= Html::submitButton('<i class="bi bi-save"></i> Сохранить', ['class' => 'btn btn-primary']) ?>
                <?= Html::a('Отмена', $model->isNewRecord ? ['index'] : ['view', 'id' => $model->id], [
                    'class' => 'btn btn-outline-secondary',
                ]) ?>
            </div>
        </div>
    </div>

    <?php if (!$model->isNewRecord) : ?>
        <div class="card mt-3">
            <div class="card-body small text-muted">
                <div>Создана: <?= Html::encode($model->getFormattedCreatedAt()) ?></div>
                <?php if (!empty($model->updated_at)) : ?>
                    <div>Изменена: <?= Html::encode($model->getFormattedUpdatedAt()) ?></div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php ActiveForm::end(); ?>
