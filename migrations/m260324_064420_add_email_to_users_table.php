<?php

use yii\db\Migration;

class m260324_064420_add_email_to_users_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        // Добавляем email после id, с ограничением длины и комментарием
        $this->addColumn(
            '{{%users}}',
            'email',
            $this->string(255)
                ->after('username')
                ->notNull()
                ->comment('Email пользователя')
                ->defaultValue('user@mail.ru')
        );

        // Создаём индекс для быстрого поиска
        $this->createIndex(
            'idx-users-email',
            '{{%users}}',
            'email'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        // Удаляем индекс
        $this->dropIndex('idx-users-email', '{{%users}}');

        // Удаляем столбец
        $this->dropColumn('{{%users}}', 'email');
    }


}
