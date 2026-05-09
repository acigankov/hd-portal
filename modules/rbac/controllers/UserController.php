<?php

namespace app\modules\rbac\controllers;

use app\models\User;
use app\models\forms\UserForm;
use Yii;
use yii\bootstrap5\ActiveForm;
use yii\db\Exception;
use yii\filters\VerbFilter;
use yii\helpers\VarDumper;
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
     * One user
     *
     * @param  $id
     * @return string
     */
    public function actionView($id): string
    {

        $model = $this->findModel($id);

        return $this->render('view', ['model' => $model]);
    }

    /**
     * Render user create poge
     *
     * @return mixed
     */
    public function actionCreate(): mixed
    {
        $model = new UserForm();

        $model->scenario = 'create';

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect('index');
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Изменение пользователя
     *
     * @param $id
     * @return string|array|Response
     * @throws Exception
     */
    public function actionUpdate($id)
    {
        $user = $this->findModel($id);
        $model = new UserForm();

        // Устанавливаем сценарий update
        $model->scenario = 'update';

        // Загружаем данные пользователя в форму
        $model->loadFromUser($user);

        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {

                if ($model->saveToUser($user)) {
                    Yii::$app->session->setFlash('success', 'Пользователь успешно обновлен');
                    return $this->redirect(['view', 'id' => $user->id]);
                } else {
                    Yii::$app->session->setFlash('error', 'Ошибка при сохранении пользователя');
                }
            }
        }

        return $this->render('update', [
            'model' => $model,
            'user' => $user,
        ]);
    }

    /**
     * Удаление пользователя (возвращает JSON для AJAX или делает редирект)
     *
     * @param $id
     * @return string|array|Response
     * @throws NotFoundHttpException
     */
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