<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $ticket app\models\Ticket */
/* @var $mailbox app\models\Mailbox */
/* @var $text string Текст ответа специалиста */

// Текст ответа выводится через Html::encode: письмо не должно содержать
// разметку, введённую в поле ответа.
?>
<div style="font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #222;">
    <p><?= nl2br(Html::encode($text)) ?></p>

    <?php if (!empty($mailbox->signature)) : ?>
        <p style="color: #555;"><?= nl2br(Html::encode($mailbox->signature)) ?></p>
    <?php endif; ?>

    <hr style="border: none; border-top: 1px solid #ddd; margin: 16px 0;">

    <p style="font-size: 12px; color: #777;">
        Заявка <strong><?= Html::encode($ticket->ticket_number) ?></strong>:
        <?= Html::encode($ticket->subject) ?><br>
        Чтобы дополнить обращение, просто ответьте на это письмо —
        ваш ответ попадёт в ту же заявку.
    </p>
</div>
