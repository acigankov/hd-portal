<?php

use yii\db\Migration;

/**
 * Привязка заявок к почтовому каналу и признаки публичности/отправки у ответов.
 *
 * tickets.mailbox_id нужен, чтобы отвечать заявителю с того же адреса, на
 * который пришло обращение. ticket_replies.is_public отделяет внутренние
 * заметки от ответов заявителю: наружу уходят только публичные записи.
 * Существующие ответы помечаются внутренними — иначе после включения почтовой
 * отправки старая переписка могла бы уехать клиентам.
 */
class m260903_100200_add_mail_fields_to_tickets_and_replies extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%tickets}}', 'mailbox_id', $this->integer()->null()->after('organization_id')
            ->comment('Почтовый ящик, через который поступило обращение'));
        $this->createIndex('idx_tickets_mailbox_id', '{{%tickets}}', 'mailbox_id');
        $this->addForeignKey('fk_tickets_mailbox_id', '{{%tickets}}', 'mailbox_id', '{{%mailboxes}}', 'id', 'SET NULL', 'CASCADE');

        $this->addColumn('{{%ticket_replies}}', 'is_public', $this->boolean()->notNull()->defaultValue(false)
            ->comment('1 — ответ заявителю (может уйти по email), 0 — внутренняя заметка'));
        $this->addColumn('{{%ticket_replies}}', 'email_status', $this->string(20)->null()
            ->comment('Состояние отправки: queued | sent | failed | null, если письмо не требуется'));
        $this->addColumn('{{%ticket_replies}}', 'email_sent_at', $this->dateTime()->null()
            ->comment('Когда ответ ушёл заявителю'));

        $this->createIndex('idx_ticket_replies_is_public', '{{%ticket_replies}}', 'is_public');
        $this->createIndex('idx_ticket_replies_email_status', '{{%ticket_replies}}', 'email_status');
    }

    public function safeDown()
    {
        $this->dropIndex('idx_ticket_replies_email_status', '{{%ticket_replies}}');
        $this->dropIndex('idx_ticket_replies_is_public', '{{%ticket_replies}}');
        $this->dropColumn('{{%ticket_replies}}', 'email_sent_at');
        $this->dropColumn('{{%ticket_replies}}', 'email_status');
        $this->dropColumn('{{%ticket_replies}}', 'is_public');

        $this->dropForeignKey('fk_tickets_mailbox_id', '{{%tickets}}');
        $this->dropIndex('idx_tickets_mailbox_id', '{{%tickets}}');
        $this->dropColumn('{{%tickets}}', 'mailbox_id');
    }
}
