<?php

use yii\db\Migration;

/**
 * Создание таблицы проблем и связующей таблицы с заявками
 */
class m260903_083200_create_problems_table extends Migration
{
    public function safeUp()
    {
        // Создаем таблицу проблем
        $this->createTable('{{%problems}}', [
            'id' => $this->primaryKey(),
            'problem_number' => $this->string(20)->notNull()->unique()->comment('Номер проблемы в формате prb#000001'),
            'title' => $this->string(255)->notNull()->comment('Название проблемы'),
            'description' => $this->text()->null()->comment('Описание проблемы'),
            'jira_ticket' => $this->string(255)->null()->comment('Ссылка на Jira тикет'),
            'category_id' => $this->integer()->null()->comment('ID категории'),
            'status_id' => $this->integer()->null()->comment('ID статуса'),
            'author_id' => $this->integer()->null()->comment('ID автора проблемы'),
            'responsible_id' => $this->integer()->null()->comment('ID ответственного'),
            'priority' => $this->smallInteger()->defaultValue(1)->comment('Приоритет (1-низкий, 2-средний, 3-высокий)'),
            'due_date' => $this->date()->null()->comment('Срок выполнения'),
            'status' => $this->smallInteger()->defaultValue(1)->comment('Статус записи (1 - активен, 0 - неактивен)'),
            'created_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->null()->defaultValue(null),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ]);

        // Создаем связующую таблицу проблем и заявок
        $this->createTable('{{%problem_tickets}}', [
            'id' => $this->primaryKey(),
            'problem_id' => $this->integer()->notNull()->comment('ID проблемы'),
            'ticket_id' => $this->integer()->notNull()->comment('ID заявки'),
            'created_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        // Добавляем индексы для таблицы проблем
        $this->createIndex('idx_problems_problem_number', '{{%problems}}', 'problem_number');
        $this->createIndex('idx_problems_category_id', '{{%problems}}', 'category_id');
        $this->createIndex('idx_problems_status_id', '{{%problems}}', 'status_id');
        $this->createIndex('idx_problems_author_id', '{{%problems}}', 'author_id');
        $this->createIndex('idx_problems_responsible_id', '{{%problems}}', 'responsible_id');
        $this->createIndex('idx_problems_priority', '{{%problems}}', 'priority');
        $this->createIndex('idx_problems_due_date', '{{%problems}}', 'due_date');
        $this->createIndex('idx_problems_status', '{{%problems}}', 'status');
        $this->createIndex('idx_problems_jira_ticket', '{{%problems}}', 'jira_ticket');

        // Добавляем индексы для связующей таблицы
        $this->createIndex('idx_problem_tickets_problem_id', '{{%problem_tickets}}', 'problem_id');
        $this->createIndex('idx_problem_tickets_ticket_id', '{{%problem_tickets}}', 'ticket_id');
        $this->createIndex('idx_problem_tickets_unique', '{{%problem_tickets}}', ['problem_id', 'ticket_id'], true);

        // Добавляем внешние ключи для таблицы проблем
        $this->addForeignKey(
            'fk_problems_category_id',
            '{{%problems}}',
            'category_id',
            '{{%categories}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_problems_status_id',
            '{{%problems}}',
            'status_id',
            '{{%statuses}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_problems_author_id',
            '{{%problems}}',
            'author_id',
            '{{%authors}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_problems_responsible_id',
            '{{%problems}}',
            'responsible_id',
            '{{%users}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_problems_created_by',
            '{{%problems}}',
            'created_by',
            '{{%users}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_problems_updated_by',
            '{{%problems}}',
            'updated_by',
            '{{%users}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        // Добавляем внешние ключи для связующей таблицы
        $this->addForeignKey(
            'fk_problem_tickets_problem_id',
            '{{%problem_tickets}}',
            'problem_id',
            '{{%problems}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_problem_tickets_ticket_id',
            '{{%problem_tickets}}',
            'ticket_id',
            '{{%tickets}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_problem_tickets_ticket_id', '{{%problem_tickets}}');
        $this->dropForeignKey('fk_problem_tickets_problem_id', '{{%problem_tickets}}');
        $this->dropTable('{{%problem_tickets}}');

        $this->dropForeignKey('fk_problems_updated_by', '{{%problems}}');
        $this->dropForeignKey('fk_problems_created_by', '{{%problems}}');
        $this->dropForeignKey('fk_problems_responsible_id', '{{%problems}}');
        $this->dropForeignKey('fk_problems_author_id', '{{%problems}}');
        $this->dropForeignKey('fk_problems_status_id', '{{%problems}}');
        $this->dropForeignKey('fk_problems_category_id', '{{%problems}}');
        $this->dropTable('{{%problems}}');
    }
}
