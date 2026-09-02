<?php

use yii\db\Migration;

/**
 * Вложения входящих писем.
 *
 * Файлы лежат не в базе, а в каталоге вне web-корня; в таблице хранится
 * относительный путь, исходное имя, тип и контрольная сумма. Такой подход
 * даёт три вещи:
 *  - скачивание идёт только через контроллер с проверкой прав, прямой ссылки
 *    на файл в интернете не существует;
 *  - исходное имя (в том числе кириллическое) не влияет на имя файла на диске;
 *  - по checksum видно повторную загрузку одного и того же файла.
 *
 * Заявка и запись обсуждения находятся через email_messages, поэтому прямых
 * связей с tickets здесь нет: вложение всегда принадлежит письму.
 */
class m260903_100300_create_email_attachments_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%email_attachments}}', [
            'id' => $this->primaryKey(),
            'email_message_id' => $this->integer()->notNull()->comment('Письмо, к которому приложен файл'),
            'original_name' => $this->string(255)->notNull()->comment('Имя файла из письма'),
            'mime_type' => $this->string(150)->null()->comment('Тип содержимого, определённый по файлу'),
            'size' => $this->integer()->notNull()->defaultValue(0)->comment('Размер в байтах'),
            'storage_path' => $this->string(255)->notNull()->comment('Путь относительно каталога вложений'),
            'checksum' => $this->string(64)->null()->comment('SHA-256 содержимого'),
            'is_inline' => $this->boolean()->notNull()->defaultValue(false)->comment('Встроенное изображение из HTML-письма'),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx_email_attachments_message', '{{%email_attachments}}', 'email_message_id');
        $this->createIndex('idx_email_attachments_checksum', '{{%email_attachments}}', 'checksum');

        $this->addForeignKey(
            'fk_email_attachments_message',
            '{{%email_attachments}}',
            'email_message_id',
            '{{%email_messages}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_email_attachments_message', '{{%email_attachments}}');
        $this->dropTable('{{%email_attachments}}');
    }
}
