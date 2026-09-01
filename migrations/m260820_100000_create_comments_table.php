<?php

use yii\db\Migration;

/**
 * Создание таблицы комментариев
 */
class m260820_100000_create_comments_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%comments}}', [
            'id' => $this->primaryKey(),
            'entity_type' => $this->string(50)->notNull()->comment('Тип сущности (task, ticket, issue)'),
            'entity_id' => $this->integer()->notNull()->comment('ID сущности'),
            'text' => $this->text()->notNull()->comment('Текст комментария'),
            'parent_id' => $this->integer()->null()->comment('ID родительского комментария (для ответов)'),
            'author_id' => $this->integer()->notNull()->comment('ID автора комментария'),
            'is_edited' => $this->boolean()->defaultValue(0)->comment('Флаг редактирования'),
            'created_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->null()->defaultValue(null),
        ]);

        // Добавляем индексы
        $this->createIndex('idx_comments_entity_type', '{{%comments}}', 'entity_type');
        $this->createIndex('idx_comments_entity_id', '{{%comments}}', 'entity_id');
        $this->createIndex('idx_comments_author_id', '{{%comments}}', 'author_id');
        $this->createIndex('idx_comments_created_at', '{{%comments}}', 'created_at');
        $this->createIndex('idx_comments_entity', '{{%comments}}', ['entity_type', 'entity_id']);

        // Добавляем внешние ключи
        $this->addForeignKey(
            'fk_comments_author_id',
            '{{%comments}}',
            'author_id',
            '{{%users}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_comments_parent_id',
            '{{%comments}}',
            'parent_id',
            '{{%comments}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_comments_parent_id', '{{%comments}}');
        $this->dropForeignKey('fk_comments_author_id', '{{%comments}}');
        $this->dropTable('{{%comments}}');
    }
}
