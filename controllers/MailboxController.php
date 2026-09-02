<?php

namespace app\controllers;

use app\components\mail\ImapClient;
use app\components\mail\IncomingMailProcessor;
use app\models\Mailbox;
use app\models\MailboxSearch;
use Throwable;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * Управление почтовыми каналами заявок.
 *
 * Раздел закрыт для всех, кроме администратора: здесь хранятся учётные
 * данные почтовых ящиков, а маршрутизация писем определяет, в какую
 * категорию и на кого попадут новые заявки.
 */
class MailboxController extends Controller
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
                        'allow' => true,
                        'roles' => ['admin'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'test-connection' => ['POST'],
                    'fetch-now' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Список почтовых каналов
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new MailboxSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'imapAvailable' => ImapClient::isAvailable(),
        ]);
    }

    /**
     * Карточка почтового канала
     * @param int $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
            'imapAvailable' => ImapClient::isAvailable(),
        ]);
    }

    /**
     * Подключение нового ящика
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Mailbox();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Почтовый канал подключён. Проверьте соединение перед запуском.');

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Изменение настроек ящика
     * @param int $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Настройки почтового канала сохранены.');

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Отключение ящика.
     *
     * Заявки, созданные из этого ящика, сохраняются: связь очищается,
     * чтобы не потерять историю обращений.
     *
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        Yii::$app->session->setFlash('success', 'Почтовый канал удалён. Ранее созданные заявки сохранены.');

        return $this->redirect(['index']);
    }

    /**
     * Проверка соединения с ящиком по IMAP
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionTestConnection($id)
    {
        $model = $this->findModel($id);

        try {
            $count = (new ImapClient($model))->test();
            Yii::$app->session->setFlash('success', 'IMAP-подключение успешно. Писем в папке: ' . $count . '.');
        } catch (Throwable $exception) {
            Yii::$app->session->setFlash('error', 'Ошибка IMAP: ' . $exception->getMessage());
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Разовый приём писем без ожидания cron — удобно при настройке канала
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionFetchNow($id)
    {
        $model = $this->findModel($id);
        $stats = (new IncomingMailProcessor())->processMailbox($model);

        Yii::$app->session->setFlash($stats['errors'] > 0 ? 'error' : 'success', sprintf(
            'Получено писем: %d, создано заявок: %d, добавлено ответов: %d, пропущено: %d, ошибок: %d.',
            $stats['fetched'],
            $stats['created'],
            $stats['replies'],
            $stats['skipped'],
            $stats['errors']
        ));

        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Поиск почтового канала
     * @param int $id
     * @return Mailbox
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = Mailbox::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Запрошенная страница не найдена.');
    }
}
