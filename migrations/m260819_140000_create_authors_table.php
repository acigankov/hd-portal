<?php

use yii\db\Migration;

/**
 * Создание таблицы авторов
 */
class m260819_140000_create_authors_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%authors}}', [
            'id' => $this->primaryKey(),
            'full_name' => $this->string(255)->notNull()->comment('ФИО автора'),
            'email' => $this->string(255)->null()->comment('Email автора'),
            'phone' => $this->string(50)->null()->comment('Телефон автора'),
            'organization_id' => $this->integer()->null()->comment('ID организации'),
            'position' => $this->string(255)->null()->comment('Должность'),
            'status' => $this->smallInteger()->defaultValue(1)->comment('Статус записи (1 - активен, 0 - неактивен)'),
            'created_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->null()->defaultValue(null),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ]);

        // Добавляем индексы
        $this->createIndex('idx_authors_status', '{{%authors}}', 'status');
        $this->createIndex('idx_authors_organization_id', '{{%authors}}', 'organization_id');
        
        // Добавляем внешние ключи
        $this->addForeignKey(
            'fk_authors_organization_id',
            '{{%authors}}',
            'organization_id',
            '{{%organizations}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
        
        $this->addForeignKey(
            'fk_authors_created_by',
            '{{%authors}}',
            'created_by',
            '{{%users}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
        
        $this->addForeignKey(
            'fk_authors_updated_by',
            '{{%authors}}',
            'updated_by',
            '{{%users}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_authors_updated_by', '{{%authors}}');
        $this->dropForeignKey('fk_authors_created_by', '{{%authors}}');
        $this->dropForeignKey('fk_authors_organization_id', '{{%authors}}');
        $this->dropTable('{{%authors}}');
    }
}
