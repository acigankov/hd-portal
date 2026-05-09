<?php

namespace app\modules\rbac\controllers;

use app\models\User;
use app\models\forms\UserForm;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class UserController extends Controller
{
    /**
     * Returns a list of behaviors that this component should behave as.
     *
     * @return array
     */
    public function behaviors(): array
    {
            return [
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'index' => ['get'],
                        'view' => ['get'],
                        'create' => ['get', 'post'],
                        'update' => ['get', 'post'],
                        'delete' => ['post'],
                    ],
                ]
            ];
    }

    /**
     * @param $id
     * @return User|null
     */
    protected function findModel($id)
    {
        if (($model = User::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Страница не найдена.');
    }

    /**
     * List of all users
     *
     * @return string
     */
    public function actionIndex (): string
    {

        $model = User::findAll(['status' => User::STATUS_ACTIVE]);

        return $this->render('index', ['model' => $model]);
    }

    /**
     * Render user create poge
     *
     * @return mixed
     */
    public function actionCreate(): mixed
    {
        $model = new UserForm();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect('index');
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * @param $id
     * @return string|array|Response
     * @throws NotFoundHttpException
     */
    // Удаление пользователя (возвращает JSON для AJAX или делает редирект)
    public function actionDelete($id)
    {
        $request = Yii::$app->request;
        $model = $this->findModel($id);

        // Очистка связанных данных (если нужно)
        // $model->deleteRelatedData();

        if ($model->delete()) {
            if ($request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['success' => true, 'message' => 'Пользователь удален'];
            }
            Yii::$app->session->setFlash('success', 'Пользователь успешно удален');
        } else {
            if ($request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['success' => false, 'message' => 'Не удалось удалить пользователя'];
            }
            Yii::$app->session->setFlash('error', 'Не удалось удалить пользователя');
        }

        return $this->redirect(['index']);
    }
}