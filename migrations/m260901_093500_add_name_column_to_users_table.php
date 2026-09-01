<?php

use yii\db\Migration;

/**
 * Добавление колонки name в таблицу users
 */
class m260901_093500_add_name_column_to_users_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%users}}', 'name', $this->string(128)->after('login'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%users}}', 'name');
    }
}
