<?php

use yii\widgets\LinkPager;
use yii\helpers\Html;

/**
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var string $modelClass
 * @var int $modelId
 * @var string $title
 * @var string $defaultSort
 */

$comments = $dataProvider->getModels();
$pagination = $dataProvider->getPagination();
$currentUser = Yii::$app->user->identity;
$uniqueId = 'comments-widget-' . $modelClass . '-' . $modelId;
$sortOrder = Yii::$app->request->get('sort', $defaultSort === 'ASC' ? 'ASC' : 'DESC');
$oppositeSort = $sortOrder === 'ASC' ? 'DESC' : 'ASC';
$sortLabel = $sortOrder === 'ASC' ? 'Сначала старые' : 'Сначала новые';
?>

<div class="card card-primary card-outline" id="<?= $uniqueId ?>">
    <div class="card-header">
        <h3 class="card-title"><?= Html::encode($title) ?></h3>
        <div class="card-tools">
            <!-- Кнопка сортировки -->
            <?php
            $currentParams = Yii::$app->request->queryParams;
            $currentParams['sort'] = 'created_at';
            $currentParams['dir'] = $oppositeSort;
            ?>
            <a href="<?= '?' . http_build_query($currentParams) ?>" 
               class="btn btn-tool" 
               title="Сортировать: <?= $sortLabel ?>"
               data-toggle="tooltip">
                <i class="fas fa-sort-<?= $sortOrder === 'ASC' ? 'up' : 'down' ?>"></i>
            </a>
            <!-- Кнопка сворачивания/разворачивания -->
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>

    <div class="card-body" style="display: block;">
        <!-- Форма добавления комментария -->
        <?php if ($currentUser): ?>
        <div class="mb-3">
            <?= Html::beginForm(['comment/create'], 'post', ['id' => "comment-form-{$uniqueId}", 'class' => 'comment-form']) ?>
            <?= Html::hiddenInput('entity_class', $modelClass) ?>
            <?= Html::hiddenInput('entity_id', $modelId) ?>
            <div class="form-group">
                <?= Html::textarea('text', '', [
                    'class' => 'form-control',
                    'placeholder' => 'Напишите комментарий...',
                    'rows' => 3,
                    'required' => true,
                ]) ?>
            </div>
            <div class="form-group">
                <?= Html::submitButton('Отправить', ['class' => 'btn btn-primary btn-sm']) ?>
            </div>
            <?= Html::endForm() ?>
        </div>
        <?php else: ?>
        <div class="alert alert-info">
            Только авторизованные пользователи могут оставлять комментарии.
        </div>
        <?php endif; ?>

        <!-- Список комментариев -->
        <div class="comments-list" id="comments-list-<?= $uniqueId ?>">
            <?php if (empty($comments)): ?>
            <p class="text-muted text-center">Комментариев пока нет. Будьте первым!</p>
            <?php else: ?>
            <?php foreach ($comments as $comment): ?>
            <div class="comment-item mb-3 pb-2 border-bottom" id="comment-<?= $comment->id ?>">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <div class="author-info">
                        <strong><?= Html::encode($comment->author->fullName ?? $comment->author->username ?? 'Удаленный пользователь') ?></strong>
                        <small class="text-muted ml-2">
                            <?= Yii::$app->formatter->asDatetime($comment->created_at) ?>
                        </small>
                    </div>
                    <?php if ($currentUser && $currentUser->id == $comment->user_id): ?>
                    <div class="comment-actions">
                        <button type="button" class="btn btn-tool btn-sm text-danger delete-comment" 
                                data-id="<?= $comment->id ?>" 
                                title="Удалить">
                            <i class="fas fa-trash"></i>
                        </button>
                        <button type="button" class="btn btn-tool btn-sm text-primary edit-comment" 
                                data-id="<?= $comment->id ?>" 
                                data-text="<?= Html::encode($comment->text) ?>"
                                title="Редактировать">
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="comment-text mt-2">
                    <?= nl2br(Html::encode($comment->text)) ?>
                </div>
                <!-- Форма редактирования (скрыта по умолчанию) -->
                <div class="edit-form mt-2" id="edit-form-<?= $comment->id ?>" style="display: none;">
                    <?= Html::beginForm(['comment/update', 'id' => $comment->id], 'post', ['class' => 'inline-edit-form']) ?>
                    <div class="form-group">
                        <?= Html::textarea('text', $comment->text, [
                            'class' => 'form-control',
                            'rows' => 3,
                        ]) ?>
                    </div>
                    <div class="form-group">
                        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success btn-sm']) ?>
                        <button type="button" class="btn btn-default btn-sm cancel-edit">Отмена</button>
                    </div>
                    <?= Html::endForm() ?>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- Пагинация -->
            <?php if ($pagination && $pagination->totalCount > $pagination->pageSize): ?>
            <div class="mt-3">
                <?= LinkPager::widget([
                    'pagination' => $pagination,
                    'options' => ['class' => 'pagination pagination-sm m-0 float-right'],
                    'linkContainerOptions' => ['class' => 'page-item'],
                    'linkOptions' => ['class' => 'page-link'],
                    'disabledListItemSubTagOptions' => ['class' => 'page-link'],
                    'activePageCssClass' => 'active',
                    'disabledPageCssClass' => 'disabled',
                ]) ?>
                <div class="clearfix"></div>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$script = <<<JS
(function($) {
    var widgetId = '{$uniqueId}';
    var formSelector = '#comment-form-' + widgetId;
    var commentsListSelector = '#comments-list-' + widgetId;

    // Отправка формы добавления комментария
    $(formSelector).on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var submitBtn = form.find('button[type="submit"]');
        var originalText = submitBtn.html();
        
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Перезагружаем виджет или добавляем комментарий в список
                    location.reload(); // Простой вариант - перезагрузка страницы
                } else {
                    alert(response.message || 'Ошибка при добавлении комментария');
                    submitBtn.prop('disabled', false).html(originalText);
                }
            },
            error: function(xhr) {
                var msg = 'Ошибка при добавлении комментария';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                alert(msg);
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    // Удаление комментария
    $(document).on('click', '.delete-comment', function() {
        if (!confirm('Вы уверены, что хотите удалить этот комментарий?')) {
            return;
        }
        
        var commentId = $(this).data('id');
        var commentItem = $('#comment-' + commentId);
        
        $.ajax({
            url: '/comment/delete',
            type: 'POST',
            data: { id: commentId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    commentItem.fadeOut(300, function() {
                        $(this).remove();
                        // Если комментариев не осталось, обновляем сообщение
                        if ($('.comment-item').length === 0) {
                            location.reload();
                        }
                    });
                } else {
                    alert(response.message || 'Ошибка при удалении комментария');
                }
            },
            error: function() {
                alert('Ошибка при удалении комментария');
            }
        });
    });

    // Редактирование комментария - показать форму
    $(document).on('click', '.edit-comment', function() {
        var commentId = $(this).data('id');
        var editForm = $('#edit-form-' + commentId);
        var commentText = $('.comment-text', '#comment-' + commentId);
        
        commentText.hide();
        editForm.show();
        $(this).closest('.comment-actions').hide();
    });

    // Отмена редактирования
    $(document).on('click', '.cancel-edit', function() {
        var editForm = $(this).closest('.edit-form');
        var commentId = editForm.attr('id').replace('edit-form-', '');
        var commentItem = $('#comment-' + commentId);
        
        editForm.hide();
        commentItem.find('.comment-text').show();
        commentItem.find('.comment-actions').show();
    });

    // Сохранение отредактированного комментария
    $(document).on('submit', '.inline-edit-form', function(e) {
        e.preventDefault();
        var form = $(this);
        var urlParts = form.attr('action').split('id=');
        var commentId = urlParts[1] || '';
        var submitBtn = form.find('button[type="submit"]');
        var originalText = submitBtn.html();
        
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.message || 'Ошибка при сохранении изменений');
                    submitBtn.prop('disabled', false).html(originalText);
                }
            },
            error: function() {
                alert('Ошибка при сохранении изменений');
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
})(jQuery);
JS;

$this->registerJs($script, \yii\web\View::POS_END);
?>
