<?php

/* @var $this yii\web\View */
/* @var $ticket app\models\Ticket */
/* @var $mailbox app\models\Mailbox */
/* @var $text string Текст ответа специалиста */

echo $text . "\n";

if (!empty($mailbox->signature)) {
    echo "\n" . $mailbox->signature . "\n";
}

echo "\n-- \n";
echo 'Заявка ' . $ticket->ticket_number . ': ' . $ticket->subject . "\n";
echo "Чтобы дополнить обращение, ответьте на это письмо — ответ попадёт в ту же заявку.\n";
