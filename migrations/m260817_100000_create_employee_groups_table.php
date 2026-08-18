<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%employee_groups}}`.
 */
class m260817_100000_create_employee_groups_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%employee_groups}}', [
            'id' => $this->primaryKey()->notNull(),
            'name' => $this->string(255)->notNull()->comment('Название группы'),
            'description' => $this->text()->comment('Описание группы'),
            'organization_id' => $this->integer()->comment('ID организации'),
            'status' => $this->smallInteger()->defaultValue(1)->comment('Статус (1 - активна, 0 - неактивна)'),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->comment('ID пользователя создателя'),
            'updated_by' => $this->integer()->comment('ID пользователя редактора'),
        ]);

        // Добавляем индекс на название для быстрого поиска
        $this->createIndex('idx-employee_groups-name', '{{%employee_groups}}', 'name');
        
        // Добавляем индекс на organization_id
        $this->createIndex('idx-employee_groups-organization_id', '{{%employee_groups}}', 'organization_id');
        
        // Добавляем внешний ключ на таблицу организаций
        $this->addForeignKey(
            'fk-employee_groups-organization_id',
            '{{%employee_groups}}',
            'organization_id',
            '{{%organizations}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
        
        // Добавляем внешний ключ на таблицу пользователей для created_by
        $this->addForeignKey(
            'fk-employee_groups-created_by',
            '{{%employee_groups}}',
            'created_by',
            '{{%users}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
        
        // Добавляем внешний ключ на таблицу пользователей для updated_by
        $this->addForeignKey(
            'fk-employee_groups-updated_by',
            '{{%employee_groups}}',
            'updated_by',
            '{{%users}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-employee_groups-updated_by', '{{%employee_groups}}');
        $this->dropForeignKey('fk-employee_groups-created_by', '{{%employee_groups}}');
        $this->dropForeignKey('fk-employee_groups-organization_id', '{{%employee_groups}}');
        $this->dropIndex('idx-employee_groups-organization_id', '{{%employee_groups}}');
        $this->dropIndex('idx-employee_groups-name', '{{%employee_groups}}');
        $this->dropTable('{{%employee_groups}}');
    }
}
