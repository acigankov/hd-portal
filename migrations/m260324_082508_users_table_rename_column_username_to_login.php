<?php

use yii\db\Migration;

class m260324_082508_users_table_rename_column_username_to_login extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameColumn('{{%users}}', 'username', 'login' );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->renameColumn('{{%users}}', 'login', 'username' );
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260324_082508_users_table_rename_column_username_to_login cannot be reverted.\n";

        return false;
    }
    */
}
