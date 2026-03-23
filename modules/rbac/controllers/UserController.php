<?php

namespace app\modules\rbac\controllers;

use app\models\User;
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
     * @return mixed
     */
    public function actionIndex () {

        $model = User::findAll(['status' => User::STATUS_ACTIVE]);

        return $this->render('index', ['model' => $model]);
    }
}