<?php

use yii\db\Migration;

class m260324_092352_users_table_rename_columns_autKey_and_authToken extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameColumn('{{%users}}', 'authKey', 'auth_key' );
        $this->renameColumn('{{%users}}', 'accessToken', 'access_token' );

        // Добавляем уникальный индекс для поля auth_key
        $this->createIndex(
            'idx-users-auth_key-unique', // имя индекса
            '{{%users}}',           // таблица
            'auth_key',                // поле
            true                    // true означает уникальный индекс
        );

        // Добавляем уникальный индекс для поля access_token
        $this->createIndex(
            'idx-users-access_token-unique', // имя индекса
            '{{%users}}',           // таблица
            'access_token',                // поле
            true                    // true означает уникальный индекс
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {

        //переименовываем обртано
        $this->renameColumn('{{%users}}', 'auth_key', 'authKey' );
        $this->renameColumn('{{%users}}', 'access_token', 'accessToken' );
        // Удаляем уникальный индекс при откате
        $this->dropIndex('idx-users-auth_key-unique', '{{%users}}');
        $this->dropIndex('idx-users-access_token-unique', '{{%users}}');
    }


}
