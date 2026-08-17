<?php

use yii\db\Migration;

class m260324_070716_alter_column_email_not_nul_no_default extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        //  Меняем ограничение на NOT NULL
        $this->alterColumn('{{%users}}', 'email', $this->string(255)->notNull());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m260324_070716_alter_column_email_not_nul_no_default cannot be reverted.\n";

        return false;
    }


}
