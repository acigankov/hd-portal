<?php

namespace app\controllers;

use app\components\mail\OutgoingReplyMailer;
use app\models\Author;
use app\models\EmailAttachment;
use app\models\Mailbox;
use app\models\Organization;
use app\models\Ticket;
use app\models\TicketReply;
use app\models\TicketSearch;
use app\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * Раздел заявок: список, карточка с обсуждением, создание, редактирование,
 * ответы и смена статуса.
 */
class TicketController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index', 'view', 'attachment'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        // Заявки обрабатывает оператор; администратор тоже.
                        // Роль проверяется через User::canProcessTickets(),
                        // потому что она может быть задана и назначением RBAC,
                        // и колонкой users.role.
                        'actions' => ['create', 'update', 'reply', 'change-status'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function () {
                            return User::canProcessTickets();
                        },
                    ],
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['admin'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'reply' => ['POST'],
                    'change-status' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Список заявок
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new TicketSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'organizations' => Organization::find()->andWhere(['status' => 1])->orderBy(['name' => SORT_ASC])->all(),
            'categories' => Ticket::categoryList(),
            'statuses' => Ticket::statusList(),
            'users' => $this->specialists(),
        ]);
    }

    /**
     * Карточка заявки с обсуждением и комментариями
     * @param int $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        $reply = new TicketReply();
        $reply->ticket_id = $model->id;

        // По умолчанию ответ уходит заявителю, если это возможно: почтовые
        // заявки чаще всего требуют именно ответа, а не внутренней заметки.
        $reply->is_public = $model->getCanEmailAuthor();

        return $this->render('view', [
            'model' => $model,
            'replies' => $model->replies,
            'reply' => $reply,
            'statuses' => Ticket::statusList(),
            'canProcess' => User::canProcessTickets(),
            'canEmailAuthor' => $model->getCanEmailAuthor(),
            'attachments' => EmailAttachment::groupedByReply((int)$model->id),
        ]);
    }

    /**
     * Скачивание вложения из письма.
     *
     * Файлы лежат вне web-корня, поэтому единственный способ их получить —
     * это действие, доступное только авторизованному сотруднику. Содержимое
     * всегда отдаётся как загрузка и с нейтральным типом: письмо пришло
     * извне, и открывать его HTML или SVG в браузере на домене портала нельзя.
     *
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionAttachment($id)
    {
        $attachment = EmailAttachment::findOne((int)$id);

        if ($attachment === null) {
            throw new NotFoundHttpException('Вложение не найдено.');
        }

        $path = $attachment->getAbsolutePath();

        if ($path === null) {
            throw new NotFoundHttpException('Файл вложения не найден в хранилище.');
        }

        Yii::$app->response->headers->set('X-Content-Type-Options', 'nosniff');
        Yii::$app->response->headers->set('Content-Security-Policy', "default-src 'none'");

        return Yii::$app->response->sendFile($path, $attachment->original_name, [
            'mimeType' => 'application/octet-stream',
            'inline' => false,
        ]);
    }

    /**
     * Создание заявки
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Ticket();
        $model->priority = Ticket::PRIORITY_MEDIUM;

        if ($model->load(Yii::$app->request->post()) && $model->saveWithNumber()) {
            Yii::$app->session->setFlash('success', 'Заявка ' . $model->ticket_number . ' создана.');

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ] + $this->formData());
    }

    /**
     * Редактирование заявки
     * @param int $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $previousStatusId = $model->status_id !== null ? (int)$model->status_id : null;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $newStatusId = $model->status_id !== null ? (int)$model->status_id : null;

            // Статус можно поменять и из формы редактирования — история
            // обсуждения не должна от этого зависеть.
            if ($newStatusId !== $previousStatusId) {
                $this->logStatusChange($model, $previousStatusId, $newStatusId);
            }

            Yii::$app->session->setFlash('success', 'Заявка сохранена.');

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ] + $this->formData());
    }

    /**
     * Добавление ответа в обсуждение
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionReply($id)
    {
        $model = $this->findModel($id);

        $reply = new TicketReply();
        $reply->load(Yii::$app->request->post());

        // Служебные поля задаёт сервер: иначе сторону, автора и тип записи
        // можно было бы подменить скрытыми полями формы.
        $reply->ticket_id = $model->id;
        $reply->type = TicketReply::TYPE_REPLY;

        if ($reply->author_side === TicketReply::SIDE_CLIENT) {
            $reply->user_id = null;
            $reply->author_id = $model->author_id;
            $reply->author_name = $model->getAuthorDisplayName();
            // Запись от заявителя — часть переписки, но письмо по ней не шлётся:
            // ответ ему же самому был бы почтовой петлёй.
            $reply->is_public = true;
        } else {
            $reply->author_side = TicketReply::SIDE_OPERATOR;
            $reply->author_id = null;
            $reply->user_id = (int)Yii::$app->user->id;
            $reply->author_name = TicketReply::currentUserName();
        }

        if (!$reply->save()) {
            Yii::$app->session->setFlash('error', 'Не удалось сохранить ответ: ' . $this->firstError($reply));

            return $this->redirect(['view', 'id' => $model->id, '#' => 'discussion']);
        }

        Yii::$app->session->setFlash('success', $this->replySavedMessage($model, $reply));

        return $this->redirect(['view', 'id' => $model->id, '#' => 'discussion']);
    }

    /**
     * Смена статуса из карточки
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionChangeStatus($id)
    {
        $model = $this->findModel($id);
        $statusId = (int)Yii::$app->request->post('status_id');

        $allowed = array_map(static function ($status) {
            return (int)$status->id;
        }, Ticket::statusList());

        if (!in_array($statusId, $allowed, true)) {
            Yii::$app->session->setFlash('error', 'Такой статус недоступен для заявок.');

            return $this->redirect(['view', 'id' => $model->id]);
        }

        if ($model->changeStatus($statusId)) {
            Yii::$app->session->setFlash('success', 'Статус заявки обновлён.');
        } else {
            Yii::$app->session->setFlash('error', 'Не удалось сменить статус: ' . $this->firstError($model));
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Удаление заявки вместе с обсуждением
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->delete();

        Yii::$app->session->setFlash('success', 'Заявка удалена.');

        return $this->redirect(['index']);
    }

    /**
     * Постановка ответа в очередь и текст сообщения для сотрудника.
     *
     * Сотрудник должен сразу понимать, уйдёт ли его ответ заявителю:
     * молчаливое сохранение без отправки — частая причина потерянных обращений.
     *
     * @param Ticket $model
     * @param TicketReply $reply
     * @return string
     */
    protected function replySavedMessage(Ticket $model, TicketReply $reply): string
    {
        if (!$reply->getIsFromOperator()) {
            return 'Ответ заявителя добавлен в обсуждение.';
        }

        if (!$reply->is_public) {
            return 'Внутренняя заметка сохранена — заявителю она не отправлена.';
        }

        if ((new OutgoingReplyMailer())->queue($reply) !== null) {
            return 'Ответ сохранён и поставлен в очередь на отправку на ' . $model->author_email . '.';
        }

        if (empty($model->author_email)) {
            return 'Ответ сохранён. Email заявителя не указан, поэтому письмо не отправлено.';
        }

        return 'Ответ сохранён, но письмо не отправлено: у заявки нет почтового канала с настроенной отправкой.';
    }

    /**
     * Справочники для формы заявки
     * @return array
     */
    protected function formData(): array
    {
        return [
            'mailboxes' => Mailbox::find()->orderBy(['name' => SORT_ASC])->all(),
            'organizations' => Organization::find()->andWhere(['status' => 1])->orderBy(['name' => SORT_ASC])->all(),
            'authors' => Author::find()->andWhere(['status' => 1])->orderBy(['full_name' => SORT_ASC])->all(),
            'categories' => Ticket::categoryList(),
            'statuses' => Ticket::statusList(),
            'users' => $this->specialists(),
        ];
    }

    /**
     * Пользователи, на которых можно назначить заявку
     * @return User[]
     */
    protected function specialists(): array
    {
        return User::find()
            ->where(['status' => User::STATUS_ACTIVE])
            ->orderBy(['name' => SORT_ASC, 'login' => SORT_ASC])
            ->all();
    }

    /**
     * Системная запись о смене статуса
     * @param Ticket $model
     * @param int|null $fromId
     * @param int|null $toId
     */
    protected function logStatusChange(Ticket $model, ?int $fromId, ?int $toId): void
    {
        $entry = new TicketReply();
        $entry->ticket_id = $model->id;
        $entry->type = TicketReply::TYPE_SYSTEM;
        $entry->author_side = TicketReply::SIDE_OPERATOR;
        $entry->user_id = (int)Yii::$app->user->id;
        $entry->author_name = TicketReply::currentUserName();
        $entry->status_from_id = $fromId;
        $entry->status_to_id = $toId;

        if (!$entry->save()) {
            Yii::error('Не удалось записать смену статуса заявки: ' . print_r($entry->errors, true), __METHOD__);
        }
    }

    /**
     * Первая ошибка валидации в читаемом виде
     * @param \yii\base\Model $model
     * @return string
     */
    protected function firstError($model): string
    {
        $errors = $model->getFirstErrors();

        return $errors ? (string)reset($errors) : 'проверьте заполненные поля';
    }

    /**
     * Поиск заявки
     * @param int $id
     * @return Ticket
     * @throws NotFoundHttpException
     */
    protected function findModel($id): Ticket
    {
        $model = Ticket::find()
            ->andWhere(['id' => (int)$id])
            ->withRelations()
            ->one();

        if ($model === null) {
            throw new NotFoundHttpException('Заявка не найдена.');
        }

        return $model;
    }
}
