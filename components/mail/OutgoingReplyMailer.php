<?php

namespace app\components\mail;

use app\models\EmailAttachment;
use app\models\EmailMessage;
use app\models\Mailbox;
use app\models\Ticket;
use app\models\TicketReply;
use RuntimeException;
use Throwable;
use Yii;
use yii\symfonymailer\Mailer;

/**
 * Отправка ответов заявителю.
 *
 * Работа разделена на два шага. Интерфейс только ставит письмо в очередь
 * (queue()), а отправляет консольная команда mail/send-pending. Поэтому
 * сохранение ответа в заявке не зависит от доступности SMTP: сотрудник видит
 * свой ответ сразу, а недоставленное письмо остаётся в очереди с числом
 * попыток и текстом ошибки.
 *
 * Наружу уходят только публичные ответы (is_public = 1). Внутренние заметки
 * и системные записи в очередь не попадают.
 */
class OutgoingReplyMailer
{
    /**
     * Ставит ответ в очередь на отправку.
     *
     * @param TicketReply $reply
     * @return EmailMessage|null null, если письмо отправлять не нужно или некуда
     */
    public function queue(TicketReply $reply): ?EmailMessage
    {
        if (!$reply->is_public || $reply->getIsSystem() || !$reply->getIsFromOperator()) {
            return null;
        }

        $ticket = $reply->ticket;
        if ($ticket === null || empty($ticket->mailbox_id) || empty($ticket->author_email)) {
            return null;
        }

        $mailbox = Mailbox::findOne((int)$ticket->mailbox_id);
        if ($mailbox === null || !$mailbox->is_active || !$mailbox->getCanSend()) {
            return null;
        }

        $record = new EmailMessage();
        $record->mailbox_id = (int)$mailbox->id;
        $record->ticket_id = (int)$ticket->id;
        $record->reply_id = (int)$reply->id;
        $record->direction = EmailMessage::DIRECTION_OUTGOING;
        $record->status = EmailMessage::STATUS_QUEUED;
        $record->message_id = $this->generateMessageId($mailbox);
        $record->from_email = $mailbox->email;
        $record->from_name = $mailbox->getFromNameEffective();
        $record->to_email = $ticket->author_email;
        $record->subject = mb_substr($this->buildSubject($ticket), 0, 500);
        $record->body_text = (string)$reply->text;

        $thread = $this->threadHeaders((int)$ticket->id);
        $record->in_reply_to = $thread['in_reply_to'];
        $record->reference_ids = $thread['references'] !== [] ? implode(' ', $thread['references']) : null;

        if (!$record->save()) {
            Yii::error('Не удалось поставить ответ в очередь: ' . print_r($record->errors, true), __METHOD__);

            return null;
        }

        $reply->email_status = TicketReply::EMAIL_QUEUED;
        $reply->save(false, ['email_status']);

        return $record;
    }

    /**
     * Отправляет письма из очереди
     *
     * @param int $limit
     * @return array{sent: int, failed: int}
     */
    public function sendPending(int $limit = 50): array
    {
        $stats = ['sent' => 0, 'failed' => 0];

        $records = EmailMessage::find()
            ->andWhere([
                'direction' => EmailMessage::DIRECTION_OUTGOING,
                'status' => EmailMessage::STATUS_QUEUED,
            ])
            ->andWhere(['<', 'attempts', EmailMessage::MAX_ATTEMPTS])
            ->orderBy(['id' => SORT_ASC])
            ->limit($limit)
            ->all();

        foreach ($records as $record) {
            try {
                $this->send($record);
                $stats['sent']++;
            } catch (Throwable $exception) {
                $record->markFailed($exception->getMessage());
                $this->markReplyStatus($record, TicketReply::EMAIL_FAILED);
                $stats['failed']++;
                Yii::error('Письмо #' . $record->id . ': ' . $exception->getMessage(), __METHOD__);
            }
        }

        return $stats;
    }

    /**
     * Отправляет одно письмо из очереди
     *
     * @param EmailMessage $record
     * @throws RuntimeException
     */
    public function send(EmailMessage $record): void
    {
        $mailbox = $record->mailbox;
        if ($mailbox === null || !$mailbox->getCanSend()) {
            throw new RuntimeException('У ящика письма не заданы параметры SMTP.');
        }

        $ticket = $record->ticket;
        if ($ticket === null) {
            throw new RuntimeException('Заявка письма не найдена.');
        }

        $mailer = $this->createMailer($mailbox);

        $message = $mailer->compose(
            ['html' => 'ticket-reply-html', 'text' => 'ticket-reply-text'],
            [
                'ticket' => $ticket,
                'mailbox' => $mailbox,
                'text' => (string)$record->body_text,
            ]
        )
            ->setFrom([$mailbox->email => $mailbox->getFromNameEffective()])
            ->setTo((string)$record->to_email)
            ->setReplyTo($mailbox->getReplyToEffective())
            ->setSubject((string)$record->subject);

        $this->applyThreadHeaders($message, $record);
        $this->attachFiles($message, $record);

        if (!$mailer->send($message)) {
            throw new RuntimeException('SMTP отклонил письмо.');
        }

        $record->markSent();
        $this->markReplyStatus($record, TicketReply::EMAIL_SENT);
    }

    /**
     * Прикладывает к письму файлы из ответа.
     *
     * Файлы берутся из хранилища в момент отправки, а не из формы:
     * отправка идёт отдельной командой и может повторяться после сбоя SMTP.
     * Потерянный файл не должен блокировать ответ: текст важнее,
     * поэтому такой случай попадает в лог, а письмо уходит.
     *
     * @param \yii\mail\MessageInterface $message
     * @param EmailMessage $record
     */
    protected function attachFiles($message, EmailMessage $record): void
    {
        if (empty($record->reply_id)) {
            return;
        }

        foreach (EmailAttachment::forReply((int)$record->reply_id) as $attachment) {
            $path = $attachment->getAbsolutePath();

            if ($path === null) {
                Yii::warning(
                    'Файл вложения #' . $attachment->id . ' не найден, письмо уйдёт без него.',
                    __METHOD__
                );

                continue;
            }

            $message->attach($path, [
                'fileName' => $attachment->original_name,
                'contentType' => $attachment->mime_type ?: 'application/octet-stream',
            ]);
        }
    }

    /**
     * Мейлер, настроенный на конкретный ящик.
     *
     * Отправлять ответы с общего системного адреса нельзя: заявитель должен
     * получить письмо с того же адреса, на который писал, и ответить туда же.
     *
     * @param Mailbox $mailbox
     * @return Mailer
     */
    public function createMailer(Mailbox $mailbox): Mailer
    {
        $scheme = $mailbox->smtp_encryption === Mailbox::ENCRYPTION_SSL ? 'smtps' : 'smtp';

        /** @var Mailer $mailer */
        $mailer = Yii::createObject([
            'class' => Mailer::class,
            'viewPath' => '@app/mail',
            'useFileTransport' => false,
            'transport' => [
                'scheme' => $scheme,
                'host' => (string)$mailbox->smtp_host,
                'port' => (int)($mailbox->smtp_port ?: ($scheme === 'smtps' ? 465 : 587)),
                'username' => $mailbox->getSmtpLoginEffective(),
                'password' => (string)$mailbox->getSmtpPasswordPlain(),
            ],
        ]);

        return $mailer;
    }

    /**
     * Проставляет заголовки почтовой цепочки.
     *
     * Message-ID генерируется заранее и хранится в журнале: по нему ответ
     * заявителя находит свою заявку. In-Reply-To и References заставляют
     * почтовые клиенты показывать переписку одной цепочкой.
     *
     * @param \yii\mail\MessageInterface $message
     * @param EmailMessage $record
     */
    protected function applyThreadHeaders($message, EmailMessage $record): void
    {
        if (!method_exists($message, 'getSymfonyEmail')) {
            return;
        }

        $headers = $message->getSymfonyEmail()->getHeaders();

        if (!empty($record->message_id)) {
            $headers->remove('Message-ID');
            $headers->addIdHeader('Message-ID', (string)$record->message_id);
        }

        if (!empty($record->in_reply_to)) {
            $headers->remove('In-Reply-To');
            $headers->addIdHeader('In-Reply-To', (string)$record->in_reply_to);
        }

        if (!empty($record->reference_ids)) {
            $references = preg_split('/\s+/', (string)$record->reference_ids, -1, PREG_SPLIT_NO_EMPTY);
            if (!empty($references)) {
                $headers->remove('References');
                $headers->addIdHeader('References', $references);
            }
        }
    }

    /**
     * Заголовки цепочки для нового исходящего письма
     *
     * @param int $ticketId
     * @return array{in_reply_to: string|null, references: string[]}
     */
    protected function threadHeaders(int $ticketId): array
    {
        $last = EmailMessage::lastForTicket($ticketId);

        if ($last === null || empty($last->message_id)) {
            return ['in_reply_to' => null, 'references' => []];
        }

        $references = !empty($last->reference_ids)
            ? preg_split('/\s+/', (string)$last->reference_ids, -1, PREG_SPLIT_NO_EMPTY)
            : [];
        $references[] = (string)$last->message_id;

        // Оставляем последние ссылки: длинная цепочка раздувает заголовок,
        // а клиентам достаточно последних идентификаторов.
        $references = array_slice(array_values(array_unique($references)), -10);

        return ['in_reply_to' => (string)$last->message_id, 'references' => $references];
    }

    /**
     * Тема письма с номером заявки: по нему ответ находит заявку, даже если
     * клиент потерял заголовки цепочки.
     *
     * @param Ticket $ticket
     * @return string
     */
    protected function buildSubject(Ticket $ticket): string
    {
        return '[' . $ticket->ticket_number . '] ' . $ticket->subject;
    }

    /**
     * Собственный Message-ID вида <tkt-12-6512ab...@company.ru>
     * @param Mailbox $mailbox
     * @return string
     */
    protected function generateMessageId(Mailbox $mailbox): string
    {
        $domain = substr(strrchr((string)$mailbox->email, '@') ?: '@hd-portal.local', 1);

        return 'hd-' . bin2hex(random_bytes(12)) . '@' . $domain;
    }

    /**
     * Синхронизирует состояние отправки в записи обсуждения
     * @param EmailMessage $record
     * @param string $status
     */
    protected function markReplyStatus(EmailMessage $record, string $status): void
    {
        $reply = $record->reply;
        if ($reply === null) {
            return;
        }

        $reply->email_status = $status;
        $reply->email_sent_at = $status === TicketReply::EMAIL_SENT ? date('Y-m-d H:i:s') : $reply->email_sent_at;
        $reply->save(false, ['email_status', 'email_sent_at']);
    }
}
