<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%organizations}}`.
 */
class m260817_083851_create_organizations_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%organizations}}', [
            'id' => $this->primaryKey()->notNull(),
            'name' => $this->string(255)->notNull()->comment('Название организации'),
            'inn' => $this->string(12)->comment('ИНН организации'),
            'kpp' => $this->string(9)->comment('КПП организации'),
            'ogrn' => $this->string(15)->comment('ОГРН организации'),
            'legal_address' => $this->text()->comment('Юридический адрес'),
            'actual_address' => $this->text()->comment('Фактический адрес'),
            'phone' => $this->string(20)->comment('Телефон'),
            'email' => $this->string(255)->comment('Email'),
            'website' => $this->string(255)->comment('Сайт'),
            'director_name' => $this->string(255)->comment('ФИО директора'),
            'status' => $this->smallInteger()->defaultValue(1)->comment('Статус (1 - активна, 0 - неактивна)'),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->comment('ID пользователя создателя'),
            'updated_by' => $this->integer()->comment('ID пользователя редактора'),
        ]);

        // Добавляем индекс на название для быстрого поиска
        $this->createIndex('idx-organizations-name', '{{%organizations}}', 'name');
        
        // Добавляем уникальный индекс на ИНН
        $this->createIndex('idx-organizations-inn', '{{%organizations}}', 'inn', true);
        
        // Добавляем внешний ключ на таблицу пользователей для created_by
        $this->addForeignKey(
            'fk-organizations-created_by',
            '{{%organizations}}',
            'created_by',
            '{{%users}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
        
        // Добавляем внешний ключ на таблицу пользователей для updated_by
        $this->addForeignKey(
            'fk-organizations-updated_by',
            '{{%organizations}}',
            'updated_by',
            '{{%users}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-organizations-updated_by', '{{%organizations}}');
        $this->dropForeignKey('fk-organizations-created_by', '{{%organizations}}');
        $this->dropIndex('idx-organizations-inn', '{{%organizations}}');
        $this->dropIndex('idx-organizations-name', '{{%organizations}}');
        $this->dropTable('{{%organizations}}');
    }
}
