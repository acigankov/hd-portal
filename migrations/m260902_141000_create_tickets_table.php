<?php

use yii\db\Migration;

/**
 * Создание таблицы заявок (тикетов).
 *
 * Заявитель хранится двумя способами одновременно:
 *  - author_id — ссылка на справочник авторов, если заявитель там есть;
 *  - author_name / author_email / author_phone — слепок контактов на момент
 *    обращения. Слепок заполняется всегда, поэтому заявку можно оформить на
 *    человека, которого в справочнике нет, и данные не «поедут», если запись
 *    в справочнике потом отредактируют.
 */
class m260902_141000_create_tickets_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%tickets}}', [
            'id' => $this->primaryKey(),
            'ticket_number' => $this->string(20)->notNull()->unique()->comment('Номер заявки в формате tkt#000001'),
            'subject' => $this->string(255)->notNull()->comment('Тема обращения'),
            'description' => $this->text()->null()->comment('Описание обращения'),
            'organization_id' => $this->integer()->null()->comment('ID организации'),
            'author_id' => $this->integer()->null()->comment('ID заявителя в справочнике авторов, если он там есть'),
            'author_name' => $this->string(255)->notNull()->comment('ФИО заявителя (слепок на момент обращения)'),
            'author_email' => $this->string(255)->null()->comment('Email заявителя'),
            'author_phone' => $this->string(50)->null()->comment('Телефон заявителя'),
            'assigned_id' => $this->integer()->null()->comment('ID назначенного специалиста'),
            'category_id' => $this->integer()->null()->comment('ID категории'),
            'status_id' => $this->integer()->null()->comment('ID статуса из справочника'),
            'priority' => $this->smallInteger()->notNull()->defaultValue(2)->comment('Приоритет (1-низкий, 2-средний, 3-высокий, 4-критичный)'),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->null()->defaultValue(null),
            'created_by' => $this->integer()->null()->comment('Кто создал заявку'),
            'updated_by' => $this->integer()->null()->comment('Кто изменил заявку последним'),
        ]);

        $this->createIndex('idx_tickets_ticket_number', '{{%tickets}}', 'ticket_number');
        $this->createIndex('idx_tickets_organization_id', '{{%tickets}}', 'organization_id');
        $this->createIndex('idx_tickets_author_id', '{{%tickets}}', 'author_id');
        $this->createIndex('idx_tickets_assigned_id', '{{%tickets}}', 'assigned_id');
        $this->createIndex('idx_tickets_category_id', '{{%tickets}}', 'category_id');
        $this->createIndex('idx_tickets_status_id', '{{%tickets}}', 'status_id');
        $this->createIndex('idx_tickets_priority', '{{%tickets}}', 'priority');
        $this->createIndex('idx_tickets_created_at', '{{%tickets}}', 'created_at');

        $this->addForeignKey('fk_tickets_organization_id', '{{%tickets}}', 'organization_id', '{{%organizations}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_tickets_author_id', '{{%tickets}}', 'author_id', '{{%authors}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_tickets_assigned_id', '{{%tickets}}', 'assigned_id', '{{%users}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_tickets_category_id', '{{%tickets}}', 'category_id', '{{%categories}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_tickets_status_id', '{{%tickets}}', 'status_id', '{{%statuses}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_tickets_created_by', '{{%tickets}}', 'created_by', '{{%users}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_tickets_updated_by', '{{%tickets}}', 'updated_by', '{{%users}}', 'id', 'SET NULL', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_tickets_updated_by', '{{%tickets}}');
        $this->dropForeignKey('fk_tickets_created_by', '{{%tickets}}');
        $this->dropForeignKey('fk_tickets_status_id', '{{%tickets}}');
        $this->dropForeignKey('fk_tickets_category_id', '{{%tickets}}');
        $this->dropForeignKey('fk_tickets_assigned_id', '{{%tickets}}');
        $this->dropForeignKey('fk_tickets_author_id', '{{%tickets}}');
        $this->dropForeignKey('fk_tickets_organization_id', '{{%tickets}}');
        $this->dropTable('{{%tickets}}');
    }
}
