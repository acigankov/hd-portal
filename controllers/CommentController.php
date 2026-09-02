<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use app\models\Comment;

/**
 * CommentController implements CRUD actions for Comment model.
 */
class CommentController extends Controller
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
                        'actions' => ['index', 'create', 'update', 'delete'],
                        'allow' => true,
                        'roles' => ['@'], // Только авторизованные пользователи
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Возвращает комментарии для сущности (AJAX)
     * @param string $entityClass класс сущности (app\models\Task, etc.)
     * @param int $entityId ID сущности
     * @param string $sort сортировка (asc/desc)
     * @return mixed
     */
    public function actionIndex($entityClass, $entityId, $sort = 'asc')
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $direction = strtolower($sort) === 'desc' ? SORT_DESC : SORT_ASC;
        
        $comments = Comment::find()
            ->forEntity($entityClass, $entityId)
            ->root()
            ->orderByDate($direction)
            ->with(['author', 'replies', 'replies.author'])
            ->all();

        return [
            'success' => true,
            'comments' => $this->renderComments($comments),
        ];
    }

    /**
     * Создает новый комментарий (AJAX)
     * @return mixed
     */
    public function actionCreate()
    {
        if (Yii::$app->request->isPost) {
            $model = new Comment();
            $model->load(Yii::$app->request->post());
            $model->author_id = Yii::$app->user->id;
            $model->is_edited = 0;

            if ($model->save()) {
                if (Yii::$app->request->isAjax) {
                    return [
                        'success' => true,
                        'message' => 'Комментарий успешно добавлен.',
                        'comment' => $this->renderComment($model),
                    ];
                }
                // Для обычных запросов - редирект назад
                return $this->redirect(Yii::$app->request->referrer ?: ['/site/index']);
            } else {
                if (Yii::$app->request->isAjax) {
                    return [
                        'success' => false,
                        'errors' => $model->getErrors(),
                        'message' => 'Ошибка при добавлении комментария.',
                    ];
                }
                // Для обычных запросов - редирект с ошибкой
                Yii::$app->session->setFlash('error', 'Ошибка при добавлении комментария.');
                return $this->redirect(Yii::$app->request->referrer ?: ['/site/index']);
            }
        }

        throw new BadRequestHttpException('Invalid request method.');
    }

    /**
     * Обновляет комментарий (AJAX)
     * @param int $id ID комментария
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        // Проверка прав на редактирование
        if (!$model->canEdit()) {
            if (Yii::$app->request->isAjax) {
                return [
                    'success' => false,
                    'message' => 'У вас нет прав для редактирования этого комментария.',
                ];
            }
            Yii::$app->session->setFlash('error', 'У вас нет прав для редактирования этого комментария.');
            return $this->redirect(Yii::$app->request->referrer ?: ['/site/index']);
        }

        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            $model->is_edited = 1;

            if ($model->save()) {
                if (Yii::$app->request->isAjax) {
                    return [
                        'success' => true,
                        'message' => 'Комментарий успешно обновлен.',
                        'comment' => $this->renderComment($model),
                    ];
                }
                // Для обычных запросов - редирект назад
                Yii::$app->session->setFlash('success', 'Комментарий успешно обновлен.');
                return $this->redirect(Yii::$app->request->referrer ?: ['/site/index']);
            } else {
                if (Yii::$app->request->isAjax) {
                    return [
                        'success' => false,
                        'errors' => $model->getErrors(),
                        'message' => 'Ошибка при сохранении изменений.',
                    ];
                }
                // Для обычных запросов - редирект с ошибкой
                Yii::$app->session->setFlash('error', 'Ошибка при сохранении изменений.');
                return $this->redirect(Yii::$app->request->referrer ?: ['/site/index']);
            }
        }

        throw new BadRequestHttpException('Invalid request method.');
    }

    /**
     * Удаляет комментарий (AJAX)
     * @param int $id ID комментария
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        // Проверка прав на удаление
        if (!$model->canDelete()) {
            if (Yii::$app->request->isAjax) {
                return [
                    'success' => false,
                    'message' => 'У вас нет прав для удаления этого комментария.',
                ];
            }
            Yii::$app->session->setFlash('error', 'У вас нет прав для удаления этого комментария.');
            return $this->redirect(Yii::$app->request->referrer ?: ['/site/index']);
        }

        if ($model->delete()) {
            if (Yii::$app->request->isAjax) {
                return [
                    'success' => true,
                    'message' => 'Комментарий успешно удален.',
                ];
            }
            // Для обычных запросов - редирект назад
            Yii::$app->session->setFlash('success', 'Комментарий успешно удален.');
            return $this->redirect(Yii::$app->request->referrer ?: ['/site/index']);
        }

        if (Yii::$app->request->isAjax) {
            return [
                'success' => false,
                'message' => 'Ошибка при удалении комментария.',
            ];
        }
        Yii::$app->session->setFlash('error', 'Ошибка при удалении комментария.');
        return $this->redirect(Yii::$app->request->referrer ?: ['/site/index']);
    }

    /**
     * Рендерит один комментарий в HTML
     * @param Comment $comment
     * @return string
     */
    private function renderComment($comment)
    {
        // Явно загружаем автора через with, чтобы избежать проблемы с read-only свойством
        $comment->populateRelation('author', $comment->getAuthor()->one());
        
        return Yii::$app->controller->renderPartial('@app/views/comment/_item', [
            'model' => $comment,
        ]);
    }

    /**
     * Рендерит список комментариев
     * @param array $comments
     * @return string
     */
    private function renderComments($comments)
    {
        $html = '';
        foreach ($comments as $comment) {
            $html .= $this->renderComment($comment);
        }
        return $html;
    }

    /**
     * Finds the Comment model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Comment the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Comment::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
