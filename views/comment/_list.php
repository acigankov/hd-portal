<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $entityType string тип сущности (task, ticket, issue) */
/* @var $entityId int ID сущности */
/* @var $comments \app\models\Comment[] массив комментариев */
/* @var $sort string сортировка (asc/desc) */

$sort = $sort ?? 'asc';
?>

<!--begin::Row-->
<div class="row">
    <!--begin::Col-->
    <div class="col">
        <div class="card" id="comments-block">
            <div class="card-header d-flex justify-content-between align-items-center" style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#comments-content" aria-expanded="true">
                <h3 class="card-title mb-0">
                    <i class="fas fa-comments me-2"></i>
                    <?= Yii::t('app', 'Comments') ?>
                    <span class="badge bg-secondary ms-2" id="comments-count"><?= count($comments) ?></span>
                </h3>
                <div class="d-flex align-items-center">
                    <div class="btn-group btn-group-sm me-2">
                        <button type="button" class="btn btn-outline-secondary sort-comments" data-sort="asc" title="<?= Yii::t('app', 'Older first') ?>">
                            <i class="fas fa-sort-amount-down"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary sort-comments" data-sort="desc" title="<?= Yii::t('app', 'Newer first') ?>">
                            <i class="fas fa-sort-amount-up"></i>
                        </button>
                    </div>
                    <i class="fas fa-chevron-down collapse-indicator"></i>
                </div>
            </div>
            <div class="card-body collapse show" id="comments-content">
                <!-- Форма добавления комментария -->
                <div class="comment-form mb-4">
                    <textarea class="form-control" id="new-comment-text" rows="3" 
                              placeholder="<?= Yii::t('app', 'Write a comment...') ?>"></textarea>
                    <div class="mt-2 text-end">
                        <button type="button" class="btn btn-primary" id="add-comment-btn">
                            <i class="fas fa-paper-plane me-1"></i>
                            <?= Yii::t('app', 'Send') ?>
                        </button>
                    </div>
                </div>

                <!-- Список комментариев -->
                <div class="comments-list" id="comments-list">
                    <?php if (empty($comments)): ?>
                        <p class="text-muted text-center" id="no-comments-msg"><?= Yii::t('app', 'No comments yet. Be the first!') ?></p>
                    <?php else: ?>
                        <?php foreach ($comments as $comment): ?>
                            <?= $this->render('_item', ['model' => $comment]) ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <!--end::Col-->
</div>
<!--end::Row-->

<?php
$this->registerCss(<<<CSS
.comments-card {
    margin-top: 20px;
}

.comment-item {
    padding: 15px;
    border-bottom: 1px solid #dee2e6;
}

.comment-item:last-child {
    border-bottom: none;
}

.comment-replies {
    background-color: #f8f9fa;
    padding: 10px !important;
    border-radius: 5px;
}

.collapse-indicator {
    transition: transform 0.3s ease;
}

.collapsed .collapse-indicator,
.collapse-indicator.rotated {
    transform: rotate(-90deg);
}
CSS
);

$this->registerJs(<<<JS
(function() {
    var entityType = '{$entityType}';
    var entityId = {$entityId};
    var currentSort = '{$sort}';
    
    // Переключение сворачивания/разворачивания
    $('#comments-block .card-header').on('click', function() {
        setTimeout(function() {
            var isCollapsed = !$('#comments-content').hasClass('show');
            $('.collapse-indicator').toggleClass('rotated', isCollapsed);
        }, 100);
    });
    
    // Сортировка комментариев
    $(document).on('click', '.sort-comments', function() {
        var newSort = $(this).data('sort');
        if (newSort !== currentSort) {
            currentSort = newSort;
            loadComments();
        }
    });
    
    // Добавление комментария
    $('#add-comment-btn').on('click', function() {
        var text = $('#new-comment-text').val().trim();
        if (!text) {
            alert('Please enter comment text');
            return;
        }
        
        $.ajax({
            url: '/comment/create',
            type: 'POST',
            data: {
                Comment: {
                    entity_class: entityType,
                    entity_id: entityId,
                    text: text
                },
                _csrf: yii.getCsrfToken()
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#new-comment-text').val('');
                    $('#no-comments-msg').hide();
                    
                    // Добавляем комментарий в список
                    if ($(response.comment).length) {
                        $('#comments-list').append(response.comment);
                    } else {
                        $('#comments-list').html(response.comment);
                    }
                    
                    updateCommentsCount();
                    
                    // Прокрутка к новому комментарию
                    $('html, body').animate({
                        scrollTop: $('#comments-list').offset().top + 200
                    }, 500);
                } else {
                    alert('Error adding comment');
                }
            },
            error: function() {
                alert('Error adding comment');
            }
        });
    });
    
    // Редактирование комментария
    $(document).on('click', '.comment-edit-btn', function() {
        var commentId = $(this).data('comment-id');
        var commentItem = $('[data-comment-id="' + commentId + '"]');
        var commentText = commentItem.find('.comment-text');
        var textarea = commentItem.find('.comment-edit-textarea');
        var editBtn = $(this);
        
        if (textarea.is(':visible')) {
            // Сохранение
            var newText = textarea.val().trim();
            if (!newText) {
                alert('Please enter comment text');
                return;
            }
            
            $.ajax({
                url: '/comment/update?id=' + commentId,
                type: 'POST',
                data: {
                    Comment: {
                        text: newText
                    },
                    _csrf: yii.getCsrfToken()
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        commentText.html(response.comment);
                        textarea.hide();
                        commentText.show();
                        editBtn.find('i').removeClass('fa-check').addClass('fa-edit');
                    } else {
                        alert(response.message || 'Error editing comment');
                    }
                },
                error: function() {
                    alert('Error editing comment');
                }
            });
        } else {
            // Режим редактирования
            textarea.val(commentText.text());
            commentText.hide();
            textarea.show();
            textarea.focus();
            editBtn.find('i').removeClass('fa-edit').addClass('fa-check');
        }
    });
    
    // Удаление комментария
    $(document).on('click', '.comment-delete-btn', function() {
        var commentId = $(this).data('comment-id');
        
        if (!confirm('Are you sure you want to delete this comment?')) {
            return;
        }
        
        $.ajax({
            url: '/comment/delete?id=' + commentId,
            type: 'POST',
            data: {
                _csrf: yii.getCsrfToken()
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('[data-comment-id="' + commentId + '"]').remove();
                    updateCommentsCount();
                    
                    if ($('#comments-list .comment-item').length === 0) {
                        $('#no-comments-msg').show();
                    }
                } else {
                    alert(response.message || 'Error deleting comment');
                }
            },
            error: function() {
                alert('Error deleting comment');
            }
        });
    });
    
    // Загрузка комментариев
    function loadComments() {
        $.ajax({
            url: '/comment/index',
            type: 'GET',
            data: {
                entityClass: entityType,
                entityId: entityId,
                sort: currentSort
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#comments-list').html(response.comments);
                    updateCommentsCount();
                }
            }
        });
    }
    
    // Обновление счетчика комментариев
    function updateCommentsCount() {
        var count = $('#comments-list .comment-item').length;
        $('#comments-count').text(count);
        if (count === 0) {
            $('#no-comments-msg').show();
        } else {
            $('#no-comments-msg').hide();
        }
    }
})();
JS
, \yii\web\View::POS_END);
?>
