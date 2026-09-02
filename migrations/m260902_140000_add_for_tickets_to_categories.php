<?php

use yii\db\Migration;

/**
 * Флаг «применяется к заявкам (тикетам)» для справочника категорий.
 *
 * В таблице statuses такая колонка уже есть (m260819_131500), в categories её
 * не было — из-за этого справочник категорий нельзя было ограничить разделом
 * заявок.
 */
class m260902_140000_add_for_tickets_to_categories extends Migration
{
    public function safeUp()
    {
        $this->addColumn(
            '{{%categories}}',
            'for_tickets',
            $this->boolean()->defaultValue(false)->after('for_problems')->comment('Применяется к заявкам (тикетам)')
        );

        // Категории, уже помеченные как «для заявок», применимы и к тикетам:
        // раздел заявок в интерфейсе называется Requests.
        $this->update('{{%categories}}', ['for_tickets' => true], ['for_requests' => true]);
    }

    public function safeDown()
    {
        $this->dropColumn('{{%categories}}', 'for_tickets');
    }
}
