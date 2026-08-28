<?php

use yii\db\Migration;

/**
 * Создание таблицы задач
 */
class m260819_160000_create_tasks_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%tasks}}', [
            'id' => $this->primaryKey(),
            'task_number' => $this->string(20)->notNull()->unique()->comment('Номер задачи в формате tsk#000001'),
            'title' => $this->string(255)->notNull()->comment('Название задачи'),
            'description' => $this->text()->null()->comment('Описание задачи'),
            'category_id' => $this->integer()->null()->comment('ID категории'),
            'status_id' => $this->integer()->null()->comment('ID статуса'),
            'author_id' => $this->integer()->null()->comment('ID автора задачи'),
            'responsible_id' => $this->integer()->null()->comment('ID ответственного'),
            'priority' => $this->smallInteger()->defaultValue(1)->comment('Приоритет (1-низкий, 2-средний, 3-высокий)'),
            'due_date' => $this->date()->null()->comment('Срок выполнения'),
            'status' => $this->smallInteger()->defaultValue(1)->comment('Статус записи (1 - активен, 0 - неактивен)'),
            'created_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->null()->defaultValue(null),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ]);

        // Добавляем индексы
        $this->createIndex('idx_tasks_task_number', '{{%tasks}}', 'task_number');
        $this->createIndex('idx_tasks_category_id', '{{%tasks}}', 'category_id');
        $this->createIndex('idx_tasks_status_id', '{{%tasks}}', 'status_id');
        $this->createIndex('idx_tasks_author_id', '{{%tasks}}', 'author_id');
        $this->createIndex('idx_tasks_responsible_id', '{{%tasks}}', 'responsible_id');
        $this->createIndex('idx_tasks_priority', '{{%tasks}}', 'priority');
        $this->createIndex('idx_tasks_due_date', '{{%tasks}}', 'due_date');
        $this->createIndex('idx_tasks_status', '{{%tasks}}', 'status');

        // Добавляем внешние ключи
        $this->addForeignKey(
            'fk_tasks_category_id',
            '{{%tasks}}',
            'category_id',
            '{{%categories}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_tasks_status_id',
            '{{%tasks}}',
            'status_id',
            '{{%statuses}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_tasks_author_id',
            '{{%tasks}}',
            'author_id',
            '{{%authors}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_tasks_responsible_id',
            '{{%tasks}}',
            'responsible_id',
            '{{%users}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_tasks_created_by',
            '{{%tasks}}',
            'created_by',
            '{{%users}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_tasks_updated_by',
            '{{%tasks}}',
            'updated_by',
            '{{%users}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_tasks_updated_by', '{{%tasks}}');
        $this->dropForeignKey('fk_tasks_created_by', '{{%tasks}}');
        $this->dropForeignKey('fk_tasks_responsible_id', '{{%tasks}}');
        $this->dropForeignKey('fk_tasks_author_id', '{{%tasks}}');
        $this->dropForeignKey('fk_tasks_status_id', '{{%tasks}}');
        $this->dropForeignKey('fk_tasks_category_id', '{{%tasks}}');
        $this->dropTable('{{%tasks}}');
    }
}
