<?php

use yii\db\Migration;

/**
 * Изменение типа поля description в таблице tasks на LONGTEXT
 * для хранения HTML-контента от редактора Quill.js
 */
class m260903_122000_alter_tasks_description_to_longtext extends Migration
{
    public function safeUp()
    {
        // Изменяем тип поля description на LONGTEXT
        $this->alterColumn('{{%tasks}}', 'description', $this->longText()->null()->comment('Описание задачи (HTML)'));
    }

    public function safeDown()
    {
        // Возвращаем тип TEXT
        $this->alterColumn('{{%tasks}}', 'description', $this->text()->null()->comment('Описание задачи'));
    }
}
