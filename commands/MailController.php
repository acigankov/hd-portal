<?php

namespace app\commands;

use app\components\mail\ImapClient;
use app\components\mail\IncomingMailProcessor;
use app\components\mail\OutgoingReplyMailer;
use app\models\Mailbox;
use Throwable;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

/**
 * Почтовый канал заявок.
 *
 * Команды рассчитаны на запуск по cron:
 *
 *   * * * * * php /var/www/hd-portal/yii mail/fetch-all
 *   * * * * * php /var/www/hd-portal/yii mail/send-pending
 *
 * Приём и отправка разнесены специально: медленный или недоступный SMTP
 * не должен мешать регистрировать новые обращения.
 */
class MailController extends Controller
{
    /** @var int Максимум писем, обрабатываемых за один запуск на один ящик */
    public $limit = 50;

    /**
     * {@inheritdoc}
     */
    public function options($actionID)
    {
        return array_merge(parent::options($actionID), ['limit']);
    }

    /**
     * {@inheritdoc}
     */
    public function optionAliases()
    {
        return array_merge(parent::optionAliases(), ['l' => 'limit']);
    }

    /**
     * Приём писем со всех активных ящиков
     * @return int
     */
    public function actionFetchAll(): int
    {
        $mailboxes = Mailbox::activeList();

        if (empty($mailboxes)) {
            $this->stdout("Активных почтовых ящиков нет.\n", Console::FG_YELLOW);

            return ExitCode::OK;
        }

        $exitCode = ExitCode::OK;
        foreach ($mailboxes as $mailbox) {
            if ($this->fetchMailbox($mailbox) !== ExitCode::OK) {
                $exitCode = ExitCode::UNSPECIFIED_ERROR;
            }
        }

        return $exitCode;
    }

    /**
     * Приём писем с одного ящика
     * @param int $mailboxId
     * @return int
     */
    public function actionFetch(int $mailboxId): int
    {
        $mailbox = Mailbox::findOne($mailboxId);

        if ($mailbox === null) {
            $this->stderr("Ящик #{$mailboxId} не найден.\n", Console::FG_RED);

            return ExitCode::DATAERR;
        }

        return $this->fetchMailbox($mailbox);
    }

    /**
     * Отправка ответов из очереди
     * @return int
     */
    public function actionSendPending(): int
    {
        $stats = (new OutgoingReplyMailer())->sendPending((int)$this->limit);

        $this->stdout("Отправлено: {$stats['sent']}, ошибок: {$stats['failed']}\n");

        return $stats['failed'] > 0 ? ExitCode::UNSPECIFIED_ERROR : ExitCode::OK;
    }

    /**
     * Проверка подключения к ящику: IMAP и, если задан, SMTP
     * @param int $mailboxId
     * @return int
     */
    public function actionTest(int $mailboxId): int
    {
        $mailbox = Mailbox::findOne($mailboxId);

        if ($mailbox === null) {
            $this->stderr("Ящик #{$mailboxId} не найден.\n", Console::FG_RED);

            return ExitCode::DATAERR;
        }

        try {
            $count = (new ImapClient($mailbox))->test();
            $this->stdout("IMAP: подключение успешно, писем в папке: {$count}\n", Console::FG_GREEN);
        } catch (Throwable $exception) {
            $this->stderr('IMAP: ' . $exception->getMessage() . "\n", Console::FG_RED);

            return ExitCode::UNSPECIFIED_ERROR;
        }

        if (!$mailbox->getCanSend()) {
            $this->stdout("SMTP: не настроен, ящик работает только на приём.\n", Console::FG_YELLOW);

            return ExitCode::OK;
        }

        $this->stdout("SMTP: параметры заданы, отправка выполняется командой mail/send-pending.\n");

        return ExitCode::OK;
    }

    /**
     * Опрос одного ящика с выводом статистики
     * @param Mailbox $mailbox
     * @return int
     */
    protected function fetchMailbox(Mailbox $mailbox): int
    {
        if (!ImapClient::isAvailable()) {
            $this->stderr("Расширение php-imap не установлено — приём почты недоступен.\n", Console::FG_RED);

            return ExitCode::UNSPECIFIED_ERROR;
        }

        $this->stdout('Ящик ' . $mailbox->email . ': ');

        $stats = (new IncomingMailProcessor())->processMailbox($mailbox, (int)$this->limit);

        $this->stdout(sprintf(
            "получено %d, новых заявок %d, ответов %d, пропущено %d, ошибок %d\n",
            $stats['fetched'],
            $stats['created'],
            $stats['replies'],
            $stats['skipped'],
            $stats['errors']
        ), $stats['errors'] > 0 ? Console::FG_YELLOW : Console::FG_GREEN);

        return $stats['errors'] > 0 ? ExitCode::UNSPECIFIED_ERROR : ExitCode::OK;
    }
}
