<?php

use yii\db\Migration;

/**
 * Добавление поля role в таблицу пользователей
 */
class m260820_120000_add_role_column_to_users_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%users}}', 'role', $this->string(20)->defaultValue('operator')->comment('Роль пользователя: admin, operator'));
        
        // Добавляем индекс для поля role
        $this->createIndex('idx-users-role', '{{%users}}', 'role');
    }

    public function safeDown()
    {
        $this->dropIndex('idx-users-role', '{{%users}}');
        $this->dropColumn('{{%users}}', 'role');
    }
}
