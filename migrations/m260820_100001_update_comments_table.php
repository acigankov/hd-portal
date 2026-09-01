<?php

use yii\db\Migration;

/**
 * Обновление таблицы комментариев: замена entity_type на entity_class
 */
class m260820_100001_update_comments_table extends Migration
{
    public function safeUp()
    {
        // Переименовываем колонку entity_type в entity_class и меняем тип
        $this->renameColumn('{{%comments}}', 'entity_type', 'entity_class');
        $this->alterColumn('{{%comments}}', 'entity_class', $this->string(255)->notNull()->comment('Класс сущности (app\models\Task, app\models\Ticket, etc.)'));
        
        // Пересоздаем индекс
        $this->dropIndex('idx_comments_entity_type', '{{%comments}}');
        $this->dropIndex('idx_comments_entity', '{{%comments}}');
        $this->createIndex('idx_comments_entity_class', '{{%comments}}', 'entity_class');
        $this->createIndex('idx_comments_entity_full', '{{%comments}}', ['entity_class', 'entity_id']);
    }

    public function safeDown()
    {
        // Откат изменений
        $this->dropIndex('idx_comments_entity_class', '{{%comments}}');
        $this->dropIndex('idx_comments_entity_full', '{{%comments}}');
        
        $this->alterColumn('{{%comments}}', 'entity_class', $this->string(50)->notNull());
        $this->renameColumn('{{%comments}}', 'entity_class', 'entity_type');
        
        $this->createIndex('idx_comments_entity_type', '{{%comments}}', 'entity_type');
        $this->createIndex('idx_comments_entity', '{{%comments}}', ['entity_type', 'entity_id']);
    }
}
