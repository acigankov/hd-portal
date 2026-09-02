<?php

use yii\db\Migration;

/**
 * Создание таблицы ответов по заявке.
 *
 * Каждый ответ — отдельная запись со ссылкой на заявку; из этих записей
 * собирается обсуждение в карточке. Сторона (operator/client) определяет, слева
 * или справа рисуется сообщение. Записи с type = system создаются автоматически
 * при смене статуса и хранят прежний и новый статус.
 */
class m260902_142000_create_ticket_replies_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%ticket_replies}}', [
            'id' => $this->primaryKey(),
            'ticket_id' => $this->integer()->notNull()->comment('ID заявки'),
            'type' => $this->string(20)->notNull()->defaultValue('reply')->comment('Тип записи: reply - ответ, system - системная запись'),
            'author_side' => $this->string(20)->notNull()->defaultValue('operator')->comment('Сторона: operator - специалист, client - заявитель'),
            'user_id' => $this->integer()->null()->comment('ID пользователя, если ответ написал специалист'),
            'author_id' => $this->integer()->null()->comment('ID заявителя из справочника, если ответ со стороны клиента'),
            'author_name' => $this->string(255)->null()->comment('Подпись автора ответа (слепок)'),
            'text' => $this->text()->null()->comment('Текст ответа'),
            'status_from_id' => $this->integer()->null()->comment('Прежний статус (для системных записей)'),
            'status_to_id' => $this->integer()->null()->comment('Новый статус (для системных записей)'),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->null()->defaultValue(null),
        ]);

        $this->createIndex('idx_ticket_replies_ticket_id', '{{%ticket_replies}}', 'ticket_id');
        $this->createIndex('idx_ticket_replies_ticket_created', '{{%ticket_replies}}', ['ticket_id', 'created_at']);
        $this->createIndex('idx_ticket_replies_type', '{{%ticket_replies}}', 'type');
        $this->createIndex('idx_ticket_replies_user_id', '{{%ticket_replies}}', 'user_id');
        $this->createIndex('idx_ticket_replies_author_id', '{{%ticket_replies}}', 'author_id');

        // Ответы живут только вместе с заявкой: при удалении заявки удаляются.
        $this->addForeignKey('fk_ticket_replies_ticket_id', '{{%ticket_replies}}', 'ticket_id', '{{%tickets}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_ticket_replies_user_id', '{{%ticket_replies}}', 'user_id', '{{%users}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_ticket_replies_author_id', '{{%ticket_replies}}', 'author_id', '{{%authors}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_ticket_replies_status_from', '{{%ticket_replies}}', 'status_from_id', '{{%statuses}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_ticket_replies_status_to', '{{%ticket_replies}}', 'status_to_id', '{{%statuses}}', 'id', 'SET NULL', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_ticket_replies_status_to', '{{%ticket_replies}}');
        $this->dropForeignKey('fk_ticket_replies_status_from', '{{%ticket_replies}}');
        $this->dropForeignKey('fk_ticket_replies_author_id', '{{%ticket_replies}}');
        $this->dropForeignKey('fk_ticket_replies_user_id', '{{%ticket_replies}}');
        $this->dropForeignKey('fk_ticket_replies_ticket_id', '{{%ticket_replies}}');
        $this->dropTable('{{%ticket_replies}}');
    }
}
