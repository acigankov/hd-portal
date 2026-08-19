<?php

use yii\db\Migration;

/**
 * Создание таблицы статусов
 */
class m260819_131500_create_statuses_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%statuses}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull()->comment('Название статуса'),
            'code' => $this->string(50)->notNull()->unique()->comment('Код статуса (латиница)'),
            'description' => $this->text()->comment('Описание статуса'),
            'color' => $this->string(20)->defaultValue('secondary')->comment('Цвет статуса (bootstrap badge class)'),
            'is_default' => $this->boolean()->defaultValue(false)->comment('Статус по умолчанию'),
            'sort_order' => $this->integer()->defaultValue(0)->comment('Порядок сортировки'),
            'for_requests' => $this->boolean()->defaultValue(false)->comment('Применяется к заявкам'),
            'for_tasks' => $this->boolean()->defaultValue(false)->comment('Применяется к задачам'),
            'for_problems' => $this->boolean()->defaultValue(false)->comment('Применяется к проблемам'),
            'for_tickets' => $this->boolean()->defaultValue(false)->comment('Применяется к тикетам'),
            'status' => $this->smallInteger()->defaultValue(1)->comment('Статус записи (1 - активен, 0 - неактивен)'),
            'created_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->null()->defaultValue(null),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ]);

        // Добавляем индексы
        $this->createIndex('idx_statuses_code', '{{%statuses}}', 'code');
        $this->createIndex('idx_statuses_sort_order', '{{%statuses}}', 'sort_order');
        $this->createIndex('idx_statuses_status', '{{%statuses}}', 'status');
        
        // Добавляем внешние ключи для created_by и updated_by
        $this->addForeignKey(
            'fk_statuses_created_by',
            '{{%statuses}}',
            'created_by',
            '{{%users}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
        
        $this->addForeignKey(
            'fk_statuses_updated_by',
            '{{%statuses}}',
            'updated_by',
            '{{%users}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_statuses_updated_by', '{{%statuses}}');
        $this->dropForeignKey('fk_statuses_created_by', '{{%statuses}}');
        $this->dropTable('{{%statuses}}');
    }
}
