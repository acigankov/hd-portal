<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;
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
     * @param string $entityType тип сущности (task, ticket, issue)
     * @param int $entityId ID сущности
     * @param string $sort сортировка (asc/desc)
     * @return mixed
     */
    public function actionIndex($entityType, $entityId, $sort = 'asc')
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $direction = strtolower($sort) === 'desc' ? SORT_DESC : SORT_ASC;
        
        $comments = Comment::find()
            ->forEntity($entityType, $entityId)
            ->root()
            ->orderByDate($direction)
            ->with(['author', 'replies.author'])
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
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (Yii::$app->request->isPost) {
            $model = new Comment();
            $model->load(Yii::$app->request->post());
            $model->author_id = Yii::$app->user->id;
            $model->is_edited = 0;

            if ($model->save()) {
                return [
                    'success' => true,
                    'comment' => $this->renderComment($model),
                ];
            } else {
                return [
                    'success' => false,
                    'errors' => $model->getErrors(),
                ];
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
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = $this->findModel($id);

        // Проверка прав на редактирование
        if (!$model->canEdit()) {
            return [
                'success' => false,
                'message' => 'У вас нет прав для редактирования этого комментария.',
            ];
        }

        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            $model->is_edited = 1;

            if ($model->save()) {
                return [
                    'success' => true,
                    'comment' => $this->renderComment($model),
                ];
            } else {
                return [
                    'success' => false,
                    'errors' => $model->getErrors(),
                ];
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
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = $this->findModel($id);

        // Проверка прав на удаление
        if (!$model->canDelete()) {
            return [
                'success' => false,
                'message' => 'У вас нет прав для удаления этого комментария.',
            ];
        }

        if ($model->delete()) {
            return [
                'success' => true,
                'message' => 'Комментарий успешно удален.',
            ];
        }

        return [
            'success' => false,
            'message' => 'Ошибка при удалении комментария.',
        ];
    }

    /**
     * Рендерит один комментарий в HTML
     * @param Comment $comment
     * @return string
     */
    private function renderComment($comment)
    {
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
