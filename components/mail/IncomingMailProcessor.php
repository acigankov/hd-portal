<?php

namespace app\components\mail;

use app\models\Author;
use app\models\EmailAttachment;
use app\models\EmailMessage;
use app\models\Mailbox;
use app\models\Ticket;
use app\models\TicketReply;
use Throwable;
use Yii;

/**
 * Регистрация заявок из входящей почты.
 *
 * Для каждого письма процессор решает одно из трёх:
 *  - письмо является ответом в уже существующей заявке — добавляется запись
 *    в обсуждение от заявителя;
 *  - письмо новое — создаётся заявка по правилам того ящика, на который оно
 *    пришло;
 *  - письмо служебное (автоответ, рассылка, письмо от самого ящика) —
 *    фиксируется в журнале со статусом skipped и заявку не создаёт.
 *
 * Всё сохранение идёт в транзакции, и только после успешного commit письмо
 * помечается прочитанным на сервере. При сбое письмо будет обработано снова.
 */
class IncomingMailProcessor
{
    /** Максимальная длина текста, попадающего в заявку */
    const MAX_TEXT_LENGTH = 20000;

    /**
     * Обрабатывает один ящик
     *
     * @param Mailbox $mailbox
     * @param int $limit Максимум писем за проход
     * @return array{fetched: int, created: int, replies: int, skipped: int, errors: int}
     */
    public function processMailbox(Mailbox $mailbox, int $limit = 50): array
    {
        $stats = ['fetched' => 0, 'created' => 0, 'replies' => 0, 'skipped' => 0, 'errors' => 0];
        $client = new ImapClient($mailbox);

        try {
            $messages = $client->fetchNew($limit);
        } catch (Throwable $exception) {
            $client->close();
            $mailbox->markChecked($exception->getMessage());
            Yii::error('Ящик ' . $mailbox->email . ': ' . $exception->getMessage(), __METHOD__);
            $stats['errors']++;

            return $stats;
        }

        $stats['fetched'] = count($messages);
        $maxUid = (int)$mailbox->last_uid;

        foreach ($messages as $message) {
            try {
                $result = $this->processMessage($mailbox, $message);
                $stats[$result] = ($stats[$result] ?? 0) + 1;

                $client->markSeen((int)$message['uid']);
                $maxUid = max($maxUid, (int)$message['uid']);
            } catch (Throwable $exception) {
                $stats['errors']++;
                Yii::error(
                    'Письмо UID ' . $message['uid'] . ' (' . $mailbox->email . '): ' . $exception->getMessage(),
                    __METHOD__
                );

                // Прерываем проход по ящику: последовательность UID не должна
                // «перескочить» проблемное письмо, иначе обращение потеряется.
                break;
            }
        }

        $client->close();

        $mailbox->last_uid = $maxUid;
        $mailbox->markChecked($stats['errors'] > 0 ? 'Часть писем не обработана, подробности в логе приложения' : null);

        return $stats;
    }

    /**
     * Обрабатывает одно письмо
     *
     * @param Mailbox $mailbox
     * @param array $message Разобранное письмо из ImapClient
     * @return string Ключ статистики: created | replies | skipped
     * @throws Throwable
     */
    public function processMessage(Mailbox $mailbox, array $message): string
    {
        $fromEmail = $message['from_email'] ? strtolower((string)$message['from_email']) : null;

        if (EmailMessage::isDuplicate((int)$mailbox->id, $message['message_id'])) {
            return 'skipped';
        }

        // Письмо от самого ящика или автоответ: заявку не создаём, но в журнал
        // пишем — иначе непонятно, почему обращение «пропало».
        if ($fromEmail === null || $message['is_auto'] || $fromEmail === strtolower((string)$mailbox->email)) {
            $this->logMessage($mailbox, $message, EmailMessage::STATUS_SKIPPED, null, null);

            return 'skipped';
        }

        $ticket = $this->resolveTicket($message);
        $text = $this->extractText($message, $ticket !== null);

        $transaction = Yii::$app->db->beginTransaction();

        try {
            if ($ticket === null) {
                $ticket = $this->createTicket($mailbox, $message, $fromEmail, $text);
                $reply = null;
                $result = 'created';
            } else {
                $reply = $this->createClientReply($ticket, $text, $fromEmail);
                $this->reopenTicket($ticket, $mailbox);
                $result = 'replies';
            }

            $record = $this->logMessage(
                $mailbox,
                $message,
                EmailMessage::STATUS_PROCESSED,
                (int)$ticket->id,
                $reply !== null ? (int)$reply->id : null
            );

            $savedFiles = $this->saveAttachments($mailbox, $record, $message['attachments'] ?? []);

            $transaction->commit();
        } catch (Throwable $exception) {
            $transaction->rollBack();
            $this->cleanupFiles($savedFiles ?? []);

            throw $exception;
        }

        return $result;
    }

    /**
     * Сохраняет вложения письма.
     *
     * Файл ложится на диск вне web-корня, в базе остаётся только путь и описание.
     * Ошибка на одном файле не должна отменять заявку целиком: текст
     * обращения важнее приложенного скриншота, поэтому сбой попадает в лог.
     *
     * @param Mailbox $mailbox
     * @param EmailMessage $record
     * @param array $attachments Список из ImapClient::fetchMessage()
     * @return string[] Относительные пути записанных файлов
     */
    protected function saveAttachments(Mailbox $mailbox, EmailMessage $record, array $attachments): array
    {
        if (empty($attachments) || $record->getIsNewRecord()) {
            return [];
        }

        $storage = new AttachmentStorage();
        $saved = [];

        foreach ($attachments as $attachment) {
            $content = (string)($attachment['content'] ?? '');

            if ($content === '') {
                continue;
            }

            try {
                $path = $storage->save((int)$mailbox->id, $content, (string)$attachment['name']);
            } catch (Throwable $exception) {
                Yii::error(
                    'Не удалось сохранить вложение «' . $attachment['name'] . '»: ' . $exception->getMessage(),
                    __METHOD__
                );

                continue;
            }

            $model = new EmailAttachment();
            $model->email_message_id = (int)$record->id;
            $model->original_name = mb_substr((string)$attachment['name'], 0, 255);
            $model->mime_type = $attachment['mime'] ?? null;
            $model->size = (int)$attachment['size'];
            $model->storage_path = $path;
            $model->checksum = hash('sha256', $content);
            $model->is_inline = !empty($attachment['is_inline']);

            if ($model->save()) {
                $saved[] = $path;
            } else {
                $storage->delete($path);
                Yii::error(
                    'Не удалось записать вложение в базу: ' . $this->errorsToString($model),
                    __METHOD__
                );
            }
        }

        return $saved;
    }

    /**
     * Удаляет файлы, записанные в откаченной транзакции.
     *
     * Файловая система не участвует в транзакции базы, поэтому после отката
     * файлы остались бы на диске без единой ссылки на них.
     *
     * @param string[] $paths
     */
    protected function cleanupFiles(array $paths): void
    {
        if (empty($paths)) {
            return;
        }

        $storage = new AttachmentStorage();

        foreach ($paths as $path) {
            $storage->delete($path);
        }
    }

    /**
     * Ищет заявку, к которой относится письмо.
     *
     * Порядок важен: заголовки надёжнее темы, потому что тему пользователь
     * может отредактировать или переслать письмо вручную.
     *
     * @param array $message
     * @return Ticket|null
     */
    protected function resolveTicket(array $message): ?Ticket
    {
        $candidates = array_merge(
            $message['in_reply_to'] !== null ? [$message['in_reply_to']] : [],
            is_array($message['references']) ? $message['references'] : []
        );

        $ticketId = EmailMessage::findTicketIdByMessageIds($candidates);

        if ($ticketId !== null) {
            $ticket = Ticket::findOne($ticketId);
            if ($ticket !== null) {
                return $ticket;
            }
        }

        return $this->findTicketBySubject((string)$message['subject']);
    }

    /**
     * Заявка по номеру в теме письма: «[tkt#000184] Re: ...»
     *
     * @param string $subject
     * @return Ticket|null
     */
    protected function findTicketBySubject(string $subject): ?Ticket
    {
        if (!preg_match('/tkt#(\d{1,12})/i', $subject, $matches)) {
            return null;
        }

        $number = Ticket::NUMBER_PREFIX . str_pad($matches[1], 6, '0', STR_PAD_LEFT);

        return Ticket::findOne(['ticket_number' => $number]);
    }

    /**
     * Создаёт заявку из письма по правилам ящика
     *
     * @param Mailbox $mailbox
     * @param array $message
     * @param string $fromEmail
     * @param string $text
     * @return Ticket
     * @throws \RuntimeException
     */
    protected function createTicket(Mailbox $mailbox, array $message, string $fromEmail, string $text): Ticket
    {
        $author = $this->resolveAuthor($mailbox, $fromEmail, (string)($message['from_name'] ?? ''));

        $ticket = new Ticket();
        $ticket->subject = $this->normalizeSubject((string)$message['subject']);
        $ticket->description = $text;
        $ticket->mailbox_id = (int)$mailbox->id;
        $ticket->author_id = $author !== null ? (int)$author->id : null;
        $ticket->author_name = $this->authorName($message, $fromEmail, $author);
        $ticket->author_email = $fromEmail;
        $ticket->author_phone = $author !== null ? $author->phone : null;
        $ticket->organization_id = $mailbox->default_organization_id
            ?: ($author !== null ? $author->organization_id : null);
        $ticket->category_id = $mailbox->default_category_id;
        $ticket->status_id = $mailbox->default_status_id;
        $ticket->assigned_id = $mailbox->default_assigned_id;
        $ticket->priority = (int)$mailbox->default_priority;

        if (!$ticket->saveWithNumber()) {
            throw new \RuntimeException('Не удалось создать заявку из письма: ' . $this->errorsToString($ticket));
        }

        return $ticket;
    }

    /**
     * Добавляет ответ заявителя в существующую заявку
     *
     * @param Ticket $ticket
     * @param string $text
     * @param string $fromEmail
     * @return TicketReply
     * @throws \RuntimeException
     */
    protected function createClientReply(Ticket $ticket, string $text, string $fromEmail): TicketReply
    {
        $reply = new TicketReply();
        $reply->ticket_id = (int)$ticket->id;
        $reply->type = TicketReply::TYPE_REPLY;
        $reply->author_side = TicketReply::SIDE_CLIENT;
        $reply->author_id = $ticket->author_id;
        $reply->author_name = $ticket->getAuthorDisplayName() ?: $fromEmail;
        $reply->text = $text;
        // Письмо заявителя — часть переписки с ним, а не внутренняя заметка.
        $reply->is_public = true;

        if (!$reply->save()) {
            throw new \RuntimeException('Не удалось сохранить ответ заявителя: ' . $this->errorsToString($reply));
        }

        return $reply;
    }

    /**
     * Возвращает заявку в работу после ответа заявителя.
     *
     * Статус меняется напрямую, без Ticket::changeStatus(): тот пишет автора
     * изменения из Yii::$app->user, которого в консольном приложении нет.
     *
     * @param Ticket $ticket
     * @param Mailbox $mailbox
     */
    protected function reopenTicket(Ticket $ticket, Mailbox $mailbox): void
    {
        $statusId = $mailbox->reopen_status_id !== null ? (int)$mailbox->reopen_status_id : null;

        if ($statusId === null || (int)$ticket->status_id === $statusId) {
            return;
        }

        $previousStatusId = $ticket->status_id !== null ? (int)$ticket->status_id : null;
        $ticket->status_id = $statusId;

        if (!$ticket->save(false, ['status_id', 'updated_at'])) {
            return;
        }

        $entry = new TicketReply();
        $entry->ticket_id = (int)$ticket->id;
        $entry->type = TicketReply::TYPE_SYSTEM;
        $entry->author_side = TicketReply::SIDE_CLIENT;
        $entry->author_name = 'Ответ по email';
        $entry->status_from_id = $previousStatusId;
        $entry->status_to_id = $statusId;
        $entry->is_public = false;

        if (!$entry->save()) {
            Yii::error('Не удалось записать смену статуса по письму: ' . $this->errorsToString($entry), __METHOD__);
        }
    }

    /**
     * Находит автора по email, при необходимости создаёт запись справочника
     *
     * @param Mailbox $mailbox
     * @param string $email
     * @param string $name
     * @return Author|null
     */
    protected function resolveAuthor(Mailbox $mailbox, string $email, string $name): ?Author
    {
        $author = Author::find()->andWhere(['email' => $email])->orderBy(['id' => SORT_ASC])->one();

        if ($author !== null) {
            return $author;
        }

        if (!$mailbox->create_authors) {
            return null;
        }

        $author = new Author();
        $author->full_name = $name !== '' ? $name : $this->nameFromEmail($email);
        $author->email = $email;
        $author->organization_id = $mailbox->default_organization_id;
        $author->status = Author::STATUS_ACTIVE;

        if (!$author->save()) {
            // Заявку всё равно оформляем: контакты заявителя есть в её слепке.
            Yii::warning('Не удалось создать автора для ' . $email . ': ' . $this->errorsToString($author), __METHOD__);

            return null;
        }

        return $author;
    }

    /**
     * Пишет письмо в журнал
     *
     * @param Mailbox $mailbox
     * @param array $message
     * @param string $status
     * @param int|null $ticketId
     * @param int|null $replyId
     * @return EmailMessage
     */
    protected function logMessage(
        Mailbox $mailbox,
        array $message,
        string $status,
        ?int $ticketId,
        ?int $replyId
    ): EmailMessage {
        $record = new EmailMessage();
        $record->mailbox_id = (int)$mailbox->id;
        $record->ticket_id = $ticketId;
        $record->reply_id = $replyId;
        $record->direction = EmailMessage::DIRECTION_INCOMING;
        $record->status = $status;
        $record->message_id = $message['message_id'];
        $record->in_reply_to = $message['in_reply_to'];
        $record->reference_ids = !empty($message['references']) ? implode(' ', $message['references']) : null;
        $record->imap_uid = (int)$message['uid'];
        $record->from_email = $message['from_email'];
        $record->from_name = $message['from_name'] !== null ? mb_substr((string)$message['from_name'], 0, 255) : null;
        $record->to_email = $message['to_email'] ?: $mailbox->email;
        $record->subject = mb_substr((string)$message['subject'], 0, 500);
        $record->body_text = $message['body_text'] !== '' ? $message['body_text'] : null;
        $record->body_html = $message['body_html'] !== '' ? $message['body_html'] : null;
        $record->raw_headers = $message['raw_headers'];
        $record->received_at = $message['date'];

        if (!$record->save()) {
            Yii::error('Не удалось записать письмо в журнал: ' . $this->errorsToString($record), __METHOD__);
        }

        return $record;
    }

    /**
     * Текст письма для заявки.
     *
     * Берётся текстовая версия, при её отсутствии — HTML, приведённый к тексту.
     * HTML специально не сохраняется в заявку: он выводится в интерфейсе и был
     * бы источником XSS.
     *
     * @param array $message
     * @param bool $isReply Для ответов обрезается цитата предыдущего письма
     * @return string
     */
    protected function extractText(array $message, bool $isReply): string
    {
        $text = (string)$message['body_text'];

        if (trim($text) === '' && (string)$message['body_html'] !== '') {
            $text = $this->htmlToText((string)$message['body_html']);
        }

        if ($isReply) {
            $text = $this->stripQuotedText($text);
        }

        $text = trim($text);

        if ($text === '') {
            $text = '(письмо без текста)';
        }

        return mb_substr($text, 0, self::MAX_TEXT_LENGTH);
    }

    /**
     * Простое приведение HTML-письма к тексту
     * @param string $html
     * @return string
     */
    protected function htmlToText(string $html): string
    {
        $html = preg_replace('#<(script|style)\b[^>]*>.*?</\1>#is', '', $html);
        $html = preg_replace('#<br\s*/?>#i', "\n", (string)$html);
        $html = preg_replace('#</(p|div|tr|li|h[1-6])>#i', "\n", (string)$html);
        $text = html_entity_decode(strip_tags((string)$html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace("/\n{3,}/", "\n\n", (string)$text);

        return trim((string)$text);
    }

    /**
     * Отрезает цитату предыдущей переписки в ответе.
     *
     * Если после обрезки ничего не осталось (клиент дописал текст внутри
     * цитаты), возвращается исходное содержимое — потерять ответ хуже,
     * чем показать его целиком с цитатой.
     *
     * @param string $text
     * @return string
     */
    protected function stripQuotedText(string $text): string
    {
        $markers = [
            '/^-{2,}\s*Original Message\s*-{2,}/mi',
            '/^-{2,}\s*Исходное сообщение\s*-{2,}/mui',
            '/^On .+ wrote:$/mi',
            '/^\d{1,2}\.\d{1,2}\.\d{2,4}.*(написал|писал).*:$/mui',
            '/^>.*$/m',
        ];

        $cut = $text;
        foreach ($markers as $pattern) {
            if (preg_match($pattern, $cut, $matches, PREG_OFFSET_CAPTURE)) {
                $cut = substr($cut, 0, $matches[0][1]);
            }
        }

        $cut = trim((string)$cut);

        return $cut !== '' ? $cut : $text;
    }

    /**
     * Тема заявки из темы письма
     * @param string $subject
     * @return string
     */
    protected function normalizeSubject(string $subject): string
    {
        $subject = trim(preg_replace('/^((re|fw|fwd|ответ|пересылка)\s*:\s*)+/iu', '', $subject) ?? $subject);
        $subject = trim(preg_replace('/\[tkt#\d+\]\s*/i', '', $subject) ?? $subject);

        if ($subject === '') {
            $subject = 'Обращение по email без темы';
        }

        return mb_substr($subject, 0, 255);
    }

    /**
     * Подпись заявителя
     * @param array $message
     * @param string $email
     * @param Author|null $author
     * @return string
     */
    protected function authorName(array $message, string $email, ?Author $author): string
    {
        $name = trim((string)($message['from_name'] ?? ''));

        if ($name === '' && $author !== null) {
            $name = (string)$author->full_name;
        }

        if ($name === '') {
            $name = $this->nameFromEmail($email);
        }

        return mb_substr($name, 0, 255);
    }

    /**
     * Имя из адреса, когда отправитель не указал его в заголовке From
     * @param string $email
     * @return string
     */
    protected function nameFromEmail(string $email): string
    {
        $local = strstr($email, '@', true);

        return $local !== false && $local !== '' ? $local : $email;
    }

    /**
     * Ошибки валидации одной строкой
     * @param \yii\base\Model $model
     * @return string
     */
    protected function errorsToString($model): string
    {
        $messages = [];
        foreach ($model->getErrors() as $attribute => $errors) {
            $messages[] = $attribute . ': ' . implode('; ', $errors);
        }

        return implode(' | ', $messages);
    }
}
