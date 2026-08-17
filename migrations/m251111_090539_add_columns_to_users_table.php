<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%users}}`.
 */
class m251111_090539_add_columns_to_users_table extends Migration
{
    /**
     * @return void
     */
    public function safeUp(): void
    {
        $this->addColumn('users', 'status', 'BOOL not null default 1');
        $this->addColumn('users', 'created_at', 'DATETIME default CURRENT_TIMESTAMP');
        $this->addColumn('users', 'updated_at', 'DATETIME default CURRENT_TIMESTAMP');
    }

    /**
     * @return void
     */

    public function safeDown(): void
    {
        $this->dropColumn('users', 'status');
        $this->dropColumn('users', 'created_at');
        $this->dropColumn('users', 'updated_at');
    }
}
