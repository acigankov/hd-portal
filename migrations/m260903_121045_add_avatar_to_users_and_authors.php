<?php

use yii\db\Migration;

/**
 * Добавление колонки avatar в таблицы users и authors
 */
class m260903_121045_add_avatar_to_users_and_authors extends Migration
{
    public function safeUp()
    {
        // Добавляем поле avatar в таблицу users
        $this->addColumn('{{%users}}', 'avatar', $this->string(255)->null()->comment('Путь к аватарке пользователя'));

        // Добавляем поле avatar в таблицу authors
        $this->addColumn('{{%authors}}', 'avatar', $this->string(255)->null()->comment('Путь к аватарке автора'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%authors}}', 'avatar');
        $this->dropColumn('{{%users}}', 'avatar');
    }
}
