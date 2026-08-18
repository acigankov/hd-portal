<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use app\models\EmployeeGroup;
use app\models\EmployeeGroupMember;
use app\models\User;
use app\models\Organization;

/**
 * EmployeeGroupController implements the CRUD actions for EmployeeGroup model.
 */
class EmployeeGroupController extends Controller
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
                        'actions' => ['index', 'view'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['create', 'update', 'delete'],
                        'allow' => true,
                        'roles' => ['admin'],
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
     * Lists all EmployeeGroup models.
     * @return mixed
     */
    public function actionIndex()
    {
        $model = EmployeeGroup::find()->all();

        return $this->render('index', [
            'model' => $model,
        ]);
    }

    /**
     * Displays a single EmployeeGroup model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        
        // Получаем всех сотрудников
        $allEmployees = User::find()->where(['status' => User::STATUS_ACTIVE])->all();
        
        // Получаем текущих сотрудников группы
        $currentMemberIds = EmployeeGroupMember::find()
            ->where(['employee_group_id' => $id])
            ->select('user_id')
            ->column();
        
        return $this->render('view', [
            'model' => $model,
            'allEmployees' => $allEmployees,
            'currentMemberIds' => $currentMemberIds,
        ]);
    }

    /**
     * Adds employees to a group.
     * @param integer $id
     * @return mixed
     */
    public function actionAddMembers($id)
    {
        $model = $this->findModel($id);
        
        if (Yii::$app->request->isPost) {
            $employeeIds = Yii::$app->request->post('employee_ids', []);
            
            // Удаляем текущих сотрудников
            EmployeeGroupMember::deleteAll(['employee_group_id' => $id]);
            
            // Добавляем новых
            if (!empty($employeeIds)) {
                $userId = Yii::$app->user->id;
                $now = date('Y-m-d H:i:s');
                
                foreach ($employeeIds as $employeeId) {
                    $member = new EmployeeGroupMember();
                    $member->employee_group_id = $id;
                    $member->user_id = $employeeId;
                    $member->created_at = $now;
                    $member->created_by = $userId;
                    $member->save(false);
                }
            }
            
            Yii::$app->session->setFlash('success', 'Сотрудники группы успешно обновлены.');
            return $this->redirect(['view', 'id' => $id]);
        }
        
        throw new NotFoundHttpException('Запрошенная страница не найдена.');
    }

    /**
     * Creates a new EmployeeGroup model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new EmployeeGroup();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Группа сотрудников успешно создана.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        // Загружаем список организаций для выпадающего списка
        $organizations = Organization::find()->where(['status' => Organization::STATUS_ACTIVE])->all();

        return $this->render('create', [
            'model' => $model,
            'organizations' => $organizations,
        ]);
    }

    /**
     * Updates an existing EmployeeGroup model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Группа сотрудников успешно обновлена.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        // Загружаем список организаций для выпадающего списка
        $organizations = Organization::find()->where(['status' => Organization::STATUS_ACTIVE])->all();

        return $this->render('update', [
            'model' => $model,
            'organizations' => $organizations,
        ]);
    }

    /**
     * Deletes an existing EmployeeGroup model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        Yii::$app->session->setFlash('success', 'Группа сотрудников успешно удалена.');
        return $this->redirect(['index']);
    }

    /**
     * Finds the EmployeeGroup model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return EmployeeGroup the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = EmployeeGroup::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Запрошенная страница не найдена.');
    }
}
