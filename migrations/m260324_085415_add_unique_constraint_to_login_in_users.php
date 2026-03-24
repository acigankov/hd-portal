<?php

use yii\db\Migration;

class m260324_085415_add_unique_constraint_to_login_in_users extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        // Добавляем уникальный индекс для поля login
        $this->createIndex(
            'idx-users-login-unique', // имя индекса
            '{{%users}}',           // таблица
            'login',                // поле
            true                    // true означает уникальный индекс
        );
    }
    /**
     * {@inheritdoc}
     */
    public function down()
    {
        // Удаляем уникальный индекс при откате
        $this->dropIndex('idx-users-login-unique', '{{%users}}');
    }
}
