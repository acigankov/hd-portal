<?php

use yii\db\Migration;

/**
 * Создание таблицы почтовых ящиков.
 *
 * Каждая запись — самостоятельный канал регистрации заявок: параметры IMAP
 * (приём писем), параметры SMTP (отправка ответов) и правила, по которым
 * оформляется новая заявка из письма. Ящиков может быть сколько угодно,
 * добавление нового не требует правок кода и конфигурации.
 *
 * Пароли хранятся зашифрованными (см. app\components\Crypt), поэтому колонки
 * названы *_password_encrypted — в них нельзя писать открытый текст.
 */
class m260903_100000_create_mailboxes_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%mailboxes}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull()->comment('Название канала: «Техподдержка», «Бухгалтерия»'),
            'email' => $this->string(255)->notNull()->comment('Адрес ящика, например support@company.ru'),
            'is_active' => $this->boolean()->notNull()->defaultValue(true)->comment('Опрашивать ящик и отправлять с него ответы'),

            // Приём писем
            'imap_host' => $this->string(255)->notNull()->comment('IMAP-сервер'),
            'imap_port' => $this->integer()->notNull()->defaultValue(993)->comment('Порт IMAP'),
            'imap_encryption' => $this->string(10)->notNull()->defaultValue('ssl')->comment('Шифрование IMAP: ssl | tls | none'),
            'imap_validate_cert' => $this->boolean()->notNull()->defaultValue(true)->comment('Проверять сертификат IMAP-сервера'),
            'imap_login' => $this->string(255)->notNull()->comment('Логин IMAP'),
            'imap_password_encrypted' => $this->text()->null()->comment('Пароль IMAP в зашифрованном виде'),
            'imap_folder' => $this->string(255)->notNull()->defaultValue('INBOX')->comment('Папка, из которой читаются письма'),

            // Отправка ответов
            'smtp_host' => $this->string(255)->null()->comment('SMTP-сервер'),
            'smtp_port' => $this->integer()->null()->defaultValue(465)->comment('Порт SMTP'),
            'smtp_encryption' => $this->string(10)->notNull()->defaultValue('ssl')->comment('Шифрование SMTP: ssl | tls | none'),
            'smtp_login' => $this->string(255)->null()->comment('Логин SMTP'),
            'smtp_password_encrypted' => $this->text()->null()->comment('Пароль SMTP в зашифрованном виде'),
            'from_name' => $this->string(255)->null()->comment('Имя отправителя в исходящих письмах'),
            'reply_to' => $this->string(255)->null()->comment('Адрес для ответов, обычно сам ящик'),
            'signature' => $this->text()->null()->comment('Подпись, добавляемая в конец письма'),

            // Правила регистрации заявки
            'default_organization_id' => $this->integer()->null()->comment('Организация для заявок из этого ящика'),
            'default_category_id' => $this->integer()->null()->comment('Категория по умолчанию'),
            'default_status_id' => $this->integer()->null()->comment('Статус новой заявки'),
            'reopen_status_id' => $this->integer()->null()->comment('Статус, в который заявка возвращается после ответа заявителя'),
            'default_assigned_id' => $this->integer()->null()->comment('Специалист по умолчанию'),
            'default_priority' => $this->smallInteger()->notNull()->defaultValue(2)->comment('Приоритет новых заявок'),
            'create_authors' => $this->boolean()->notNull()->defaultValue(true)->comment('Создавать запись в справочнике авторов для нового отправителя'),

            // Состояние синхронизации
            'last_uid' => $this->integer()->notNull()->defaultValue(0)->comment('Последний обработанный IMAP UID'),
            'last_checked_at' => $this->dateTime()->null()->comment('Время последнего успешного опроса'),
            'last_error' => $this->text()->null()->comment('Текст последней ошибки подключения или разбора'),

            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->null()->defaultValue(null),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ]);

        $this->createIndex('idx_mailboxes_email', '{{%mailboxes}}', 'email', true);
        $this->createIndex('idx_mailboxes_is_active', '{{%mailboxes}}', 'is_active');

        $this->addForeignKey('fk_mailboxes_organization', '{{%mailboxes}}', 'default_organization_id', '{{%organizations}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_mailboxes_category', '{{%mailboxes}}', 'default_category_id', '{{%categories}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_mailboxes_status', '{{%mailboxes}}', 'default_status_id', '{{%statuses}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_mailboxes_reopen_status', '{{%mailboxes}}', 'reopen_status_id', '{{%statuses}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_mailboxes_assigned', '{{%mailboxes}}', 'default_assigned_id', '{{%users}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_mailboxes_created_by', '{{%mailboxes}}', 'created_by', '{{%users}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_mailboxes_updated_by', '{{%mailboxes}}', 'updated_by', '{{%users}}', 'id', 'SET NULL', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_mailboxes_updated_by', '{{%mailboxes}}');
        $this->dropForeignKey('fk_mailboxes_created_by', '{{%mailboxes}}');
        $this->dropForeignKey('fk_mailboxes_assigned', '{{%mailboxes}}');
        $this->dropForeignKey('fk_mailboxes_reopen_status', '{{%mailboxes}}');
        $this->dropForeignKey('fk_mailboxes_status', '{{%mailboxes}}');
        $this->dropForeignKey('fk_mailboxes_category', '{{%mailboxes}}');
        $this->dropForeignKey('fk_mailboxes_organization', '{{%mailboxes}}');
        $this->dropTable('{{%mailboxes}}');
    }
}
