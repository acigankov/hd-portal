<?php

use yii\db\Migration;

/**
 * Журнал писем: и входящих, и исходящих.
 *
 * Таблица решает три задачи:
 *  - дедупликация: уникальный индекс (mailbox_id, message_id) не даёт создать
 *    вторую заявку, если IMAP отдал письмо повторно после сбоя worker'а;
 *  - поиск цепочки: по message_id отправленных писем находится заявка,
 *    к которой относится ответ заявителя (заголовки In-Reply-To / References);
 *  - очередь и аудит отправки: статус, число попыток и текст ошибки.
 */
class m260903_100100_create_email_messages_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%email_messages}}', [
            'id' => $this->primaryKey(),
            'mailbox_id' => $this->integer()->notNull()->comment('Почтовый ящик, через который прошло письмо'),
            'ticket_id' => $this->integer()->null()->comment('Заявка, к которой отнесено письмо'),
            'reply_id' => $this->integer()->null()->comment('Запись обсуждения, созданная письмом или отправленная письмом'),
            'direction' => $this->string(10)->notNull()->comment('incoming | outgoing'),
            'status' => $this->string(20)->notNull()->comment('received | processed | skipped | queued | sent | failed'),
            'message_id' => $this->string(255)->null()->comment('Заголовок Message-ID'),
            'in_reply_to' => $this->string(255)->null()->comment('Заголовок In-Reply-To'),
            'reference_ids' => $this->text()->null()->comment('Заголовок References'),
            'imap_uid' => $this->integer()->null()->comment('UID письма в папке IMAP'),
            'from_email' => $this->string(255)->null(),
            'from_name' => $this->string(255)->null(),
            'to_email' => $this->string(255)->null(),
            'subject' => $this->string(500)->null(),
            'body_text' => $this->text()->null()->comment('Текстовая версия письма'),
            'body_html' => $this->text()->null()->comment('HTML-версия письма (не выводится в интерфейсе как есть)'),
            'raw_headers' => $this->text()->null()->comment('Исходные заголовки для разбора инцидентов'),
            'attempts' => $this->integer()->notNull()->defaultValue(0)->comment('Число попыток отправки'),
            'error_message' => $this->text()->null(),
            'received_at' => $this->dateTime()->null(),
            'sent_at' => $this->dateTime()->null(),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->null()->defaultValue(null),
        ]);

        $this->createIndex('idx_email_messages_mailbox_message', '{{%email_messages}}', ['mailbox_id', 'message_id'], true);
        $this->createIndex('idx_email_messages_ticket_id', '{{%email_messages}}', 'ticket_id');
        $this->createIndex('idx_email_messages_direction', '{{%email_messages}}', 'direction');
        $this->createIndex('idx_email_messages_status', '{{%email_messages}}', 'status');
        $this->createIndex('idx_email_messages_message_id', '{{%email_messages}}', 'message_id');
        $this->createIndex('idx_email_messages_in_reply_to', '{{%email_messages}}', 'in_reply_to');

        $this->addForeignKey('fk_email_messages_mailbox', '{{%email_messages}}', 'mailbox_id', '{{%mailboxes}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_email_messages_ticket', '{{%email_messages}}', 'ticket_id', '{{%tickets}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_email_messages_reply', '{{%email_messages}}', 'reply_id', '{{%ticket_replies}}', 'id', 'SET NULL', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_email_messages_reply', '{{%email_messages}}');
        $this->dropForeignKey('fk_email_messages_ticket', '{{%email_messages}}');
        $this->dropForeignKey('fk_email_messages_mailbox', '{{%email_messages}}');
        $this->dropTable('{{%email_messages}}');
    }
}
