<?php

namespace app\modules\rbac\controllers;

use app\models\User;
use app\models\forms\UserForm;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;

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
}