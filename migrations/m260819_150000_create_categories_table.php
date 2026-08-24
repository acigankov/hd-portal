<?php

use yii\db\Migration;

/**
 * Создание таблицы категорий
 */
class m260819_150000_create_categories_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%categories}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull()->comment('Название категории'),
            'code' => $this->string(50)->notNull()->unique()->comment('Код категории (латиница)'),
            'description' => $this->text()->comment('Описание категории'),
            'color' => $this->string(20)->defaultValue('primary')->comment('Цвет категории (bootstrap badge class)'),
            'icon' => $this->string(50)->null()->comment('Иконка категории (bootstrap icon)'),
            'sort_order' => $this->integer()->defaultValue(0)->comment('Порядок сортировки'),
            'for_requests' => $this->boolean()->defaultValue(false)->comment('Применяется к заявкам'),
            'for_tasks' => $this->boolean()->defaultValue(false)->comment('Применяется к задачам'),
            'for_problems' => $this->boolean()->defaultValue(false)->comment('Применяется к проблемам'),
            'status' => $this->smallInteger()->defaultValue(1)->comment('Статус записи (1 - активен, 0 - неактивен)'),
            'created_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->null()->defaultValue(null),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ]);

        // Добавляем индексы
        $this->createIndex('idx_categories_code', '{{%categories}}', 'code');
        $this->createIndex('idx_categories_sort_order', '{{%categories}}', 'sort_order');
        $this->createIndex('idx_categories_status', '{{%categories}}', 'status');
        
        // Добавляем внешние ключи для created_by и updated_by
        $this->addForeignKey(
            'fk_categories_created_by',
            '{{%categories}}',
            'created_by',
            '{{%users}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
        
        $this->addForeignKey(
            'fk_categories_updated_by',
            '{{%categories}}',
            'updated_by',
            '{{%users}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_categories_updated_by', '{{%categories}}');
        $this->dropForeignKey('fk_categories_created_by', '{{%categories}}');
        $this->dropTable('{{%categories}}');
    }
}
