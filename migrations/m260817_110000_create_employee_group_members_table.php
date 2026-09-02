<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%employee_group_members}}` for linking employees to groups.
 */
class m260817_110000_create_employee_group_members_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%employee_group_members}}', [
            'id' => $this->primaryKey()->notNull(),
            'employee_group_id' => $this->integer()->notNull()->comment('ID группы сотрудников'),
            'user_id' => $this->integer()->notNull()->comment('ID сотрудника (пользователя)'),
            'created_at' => $this->dateTime()->notNull(),
            'created_by' => $this->integer()->comment('ID пользователя, добавившего сотрудника'),
        ]);

        // Добавляем индексы для быстрого поиска
        $this->createIndex('idx-employee_group_members-group_id', '{{%employee_group_members}}', 'employee_group_id');
        $this->createIndex('idx-employee_group_members-user_id', '{{%employee_group_members}}', 'user_id');
        
        // Добавляем уникальный индекс на комбинацию group_id и user_id
        $this->createIndex('idx-employee_group_members-unique', '{{%employee_group_members}}', ['employee_group_id', 'user_id'], true);
        
        // Добавляем внешний ключ на таблицу employee_groups
        $this->addForeignKey(
            'fk-employee_group_members-group_id',
            '{{%employee_group_members}}',
            'employee_group_id',
            '{{%employee_groups}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        
        // Добавляем внешний ключ на таблицу пользователей
        $this->addForeignKey(
            'fk-employee_group_members-user_id',
            '{{%employee_group_members}}',
            'user_id',
            '{{%users}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-employee_group_members-user_id', '{{%employee_group_members}}');
        $this->dropForeignKey('fk-employee_group_members-group_id', '{{%employee_group_members}}');
        $this->dropIndex('idx-employee_group_members-unique', '{{%employee_group_members}}');
        $this->dropIndex('idx-employee_group_members-user_id', '{{%employee_group_members}}');
        $this->dropIndex('idx-employee_group_members-group_id', '{{%employee_group_members}}');
        $this->dropTable('{{%employee_group_members}}');
    }
}
