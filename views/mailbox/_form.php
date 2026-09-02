<?php

use app\models\Category;
use app\models\Mailbox;
use app\models\Organization;
use app\models\Status;
use app\models\Ticket;
use app\models\TicketReply;
use app\models\User;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Mailbox */

$organizations = Organization::find()->andWhere(['status' => 1])->orderBy(['name' => SORT_ASC])->all();
$categories = Category::find()->orderBy(['name' => SORT_ASC])->all();
$statuses = Status::find()->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC])->all();
$users = User::find()->orderBy(['login' => SORT_ASC])->all();

$form = ActiveForm::begin([
    'id' => 'mailbox-form',
    'options' => ['class' => 'row g-3'],
    'fieldConfig' => [
        'options' => ['class' => 'mb-3'],
        'labelOptions' => ['class' => 'form-label'],
    ],
]);
?>

<div class="col-lg-6">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title mb-0">Канал</h3>
        </div>
        <div class="card-body">
            <?= $form->field($model, 'name')
                ->textInput(['maxlength' => 255, 'placeholder' => 'Техподдержка'])
                ->label('Название канала *') ?>

            <?= $form->field($model, 'email')
                ->textInput(['maxlength' => 255, 'placeholder' => 'support@company.ru'])
                ->label('Адрес ящика *') ?>

            <?= $form->field($model, 'is_active')->checkbox() ?>

            <?= $form->field($model, 'from_name')
                ->textInput(['maxlength' => 255, 'placeholder' => 'Служба поддержки'])
                ->hint('Как подписан отправитель в письмах заявителю.') ?>

            <?= $form->field($model, 'reply_to')
                ->textInput(['maxlength' => 255, 'placeholder' => 'support@company.ru'])
                ->hint('Если не заполнено, используется адрес ящика.') ?>

            <?= $form->field($model, 'signature')
                ->textarea(['rows' => 3, 'placeholder' => "С уважением,\nслужба поддержки"]) ?>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h3 class="card-title mb-0">Приём писем (IMAP)</h3>
        </div>
        <div class="card-body">
            <?= $form->field($model, 'imap_host')
                ->textInput(['maxlength' => 255, 'placeholder' => 'imap.company.ru'])
                ->label('IMAP-сервер *') ?>

            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($model, 'imap_port')->textInput(['type' => 'number']) ?>
                </div>
                <div class="col-md-8">
                    <?= $form->field($model, 'imap_encryption')->dropDownList(Mailbox::encryptionList()) ?>
                </div>
            </div>

            <?= $form->field($model, 'imap_login')
                ->textInput(['maxlength' => 255, 'autocomplete' => 'off'])
                ->label('Логин IMAP *') ?>

            <?= $form->field($model, 'imap_password')
                ->passwordInput(['maxlength' => 255, 'autocomplete' => 'new-password'])
                ->hint($model->isNewRecord
                    ? 'Пароль хранится в базе в зашифрованном виде.'
                    : 'Оставьте поле пустым, чтобы сохранить текущий пароль.') ?>

            <?= $form->field($model, 'imap_folder')->textInput(['maxlength' => 255, 'placeholder' => 'INBOX']) ?>

            <?= $form->field($model, 'imap_validate_cert')
                ->checkbox()
                ->hint('Отключайте только для внутренних серверов с самоподписанным сертификатом.') ?>
        </div>
    </div>
</div>

<div class="col-lg-6">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title mb-0">Отправка ответов (SMTP)</h3>
        </div>
        <div class="card-body">
            <p class="text-muted small">
                Без SMTP-сервера канал работает только на приём: заявки будут создаваться,
                но ответы заявителю по email не уйдут.
            </p>

            <?= $form->field($model, 'smtp_host')->textInput(['maxlength' => 255, 'placeholder' => 'smtp.company.ru']) ?>

            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($model, 'smtp_port')->textInput(['type' => 'number']) ?>
                </div>
                <div class="col-md-8">
                    <?= $form->field($model, 'smtp_encryption')->dropDownList(Mailbox::encryptionList()) ?>
                </div>
            </div>

            <?= $form->field($model, 'smtp_login')
                ->textInput(['maxlength' => 255, 'autocomplete' => 'off'])
                ->hint('Если не заполнено, используется логин IMAP.') ?>

            <?= $form->field($model, 'smtp_password')
                ->passwordInput(['maxlength' => 255, 'autocomplete' => 'new-password'])
                ->hint($model->isNewRecord
                    ? 'Если не заполнено, используется пароль IMAP.'
                    : 'Оставьте поле пустым, чтобы сохранить текущий пароль.') ?>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h3 class="card-title mb-0">Маршрутизация новых заявок</h3>
        </div>
        <div class="card-body">
            <?= $form->field($model, 'default_organization_id')
                ->dropDownList(ArrayHelper::map($organizations, 'id', 'name'), ['prompt' => 'Не указана']) ?>

            <?= $form->field($model, 'default_category_id')
                ->dropDownList(ArrayHelper::map($categories, 'id', 'name'), ['prompt' => 'Без категории']) ?>

            <?= $form->field($model, 'default_status_id')
                ->dropDownList(ArrayHelper::map($statuses, 'id', 'name'), ['prompt' => 'Статус по умолчанию из справочника']) ?>

            <?= $form->field($model, 'reopen_status_id')
                ->dropDownList(ArrayHelper::map($statuses, 'id', 'name'), ['prompt' => 'Не менять статус'])
                ->hint('Статус, в который вернётся заявка, когда заявитель ответит письмом.') ?>

            <?= $form->field($model, 'default_assigned_id')
                ->dropDownList(
                    ArrayHelper::map($users, 'id', static function ($user) {
                        return TicketReply::userName($user);
                    }),
                    ['prompt' => 'Не назначать']
                ) ?>

            <?= $form->field($model, 'default_priority')->dropDownList(Ticket::priorityList()) ?>

            <?= $form->field($model, 'create_authors')
                ->checkbox()
                ->hint('Новый отправитель будет добавлен в справочник авторов автоматически.') ?>

            <div class="d-flex gap-2">
                <?= Html::submitButton('<i class="bi bi-save"></i> Сохранить', ['class' => 'btn btn-primary']) ?>
                <?= Html::a('Отмена', $model->isNewRecord ? ['index'] : ['view', 'id' => $model->id], [
                    'class' => 'btn btn-outline-secondary',
                ]) ?>
            </div>
        </div>
    </div>
</div>

<?php ActiveForm::end(); ?>
