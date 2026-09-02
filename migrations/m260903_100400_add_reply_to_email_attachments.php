<?php

use yii\db\Migration;

/**
 * Файлы, которые сотрудник прикладывает к ответу заявителю.
 *
 * До этой миграции вложение всегда принадлежало входящему письму. Теперь оно
 * может принадлежать записи обсуждения: сотрудник прикладывает файл в форме
 * ответа, файл сразу виден в заявке, а письмо с ним уходит позже — из очереди
 * отправки. Поэтому email_message_id становится необязательным, а связь
 * с ticket_replies — прямой.
 */
class m260903_100400_add_reply_to_email_attachments extends Migration
{
    public function safeUp()
    {
        $this->dropForeignKey('fk_email_attachments_message', '{{%email_attachments}}');

        $this->alterColumn(
            '{{%email_attachments}}',
            'email_message_id',
            $this->integer()->null()->comment('Входящее письмо, если файл пришёл по почте')
        );

        $this->addColumn(
            '{{%email_attachments}}',
            'reply_id',
            $this->integer()->null()->after('email_message_id')->comment('Запись обсуждения, если файл приложил сотрудник')
        );

        $this->createIndex('idx_email_attachments_reply', '{{%email_attachments}}', 'reply_id');

        $this->addForeignKey(
            'fk_email_attachments_message',
            '{{%email_attachments}}',
            'email_message_id',
            '{{%email_messages}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_email_attachments_reply',
            '{{%email_attachments}}',
            'reply_id',
            '{{%ticket_replies}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_email_attachments_reply', '{{%email_attachments}}');
        $this->dropIndex('idx_email_attachments_reply', '{{%email_attachments}}');
        $this->dropColumn('{{%email_attachments}}', 'reply_id');

        $this->dropForeignKey('fk_email_attachments_message', '{{%email_attachments}}');
        $this->alterColumn(
            '{{%email_attachments}}',
            'email_message_id',
            $this->integer()->notNull()->comment('Письмо, к которому приложен файл')
        );
        $this->addForeignKey(
            'fk_email_attachments_message',
            '{{%email_attachments}}',
            'email_message_id',
            '{{%email_messages}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }
}
