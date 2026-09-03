<?php

namespace app\controllers;

use app\models\Ticket;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use app\models\Problem;
use app\models\ProblemSearch;
use app\models\Category;
use app\models\Status;
use app\models\Author;
use app\models\User;

/**
 * ProblemController implements the CRUD actions for Problem model.
 */
class ProblemController extends Controller
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
     * Lists all Problem models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ProblemSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        
        // Получаем все категории и статусы для отображения (чтобы избежать ошибок при доступе к свойствам)
        $categories = \yii\helpers\ArrayHelper::index(Category::find()->active()->all(), 'id');
        $statuses = \yii\helpers\ArrayHelper::index(Status::find()->active()->all(), 'id');
        $users = \yii\helpers\ArrayHelper::index(User::find()->where(['status' => User::STATUS_ACTIVE])->all(), 'id');

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'categories' => $categories,
            'statuses' => $statuses,
            'users' => $users,
        ]);
    }

    /**
     * Displays a single Problem model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        
        // Получаем комментарии для проблемы
        $comments = \app\models\Comment::find()
            ->forEntity('problem', $model->id)
            ->root()
            ->orderByDate(SORT_ASC)
            ->with(['author', 'replies.author'])
            ->all();

        return $this->render('view', [
            'model' => $model,
            'comments' => $comments,
        ]);
    }

    /**
     * Creates a new Problem model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Problem();
        
        // Получаем списки для выпадающих списков
        $categories = Category::find()->active()->all();
        $statuses = Status::find()->active()->all();
        $authors = Author::find()->active()->all();
        $users = User::find()->where(['status' => User::STATUS_ACTIVE])->all();
        $tickets = Ticket::find()->orderBy(['id' => SORT_DESC])->limit(100)->all();

        if ($model->load(Yii::$app->request->post())) {
            // Сохраняем привязанные заявки
            $ticketIds = Yii::$app->request->post('Problem')['ticket_ids'] ?? [];
            
            if ($model->save()) {
                // Сохраняем связи с заявками
                $this->saveProblemTickets($model, $ticketIds);
                
                Yii::$app->session->setFlash('success', Yii::t('app', 'Problem successfully created.'));
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
            'categories' => $categories,
            'statuses' => $statuses,
            'authors' => $authors,
            'users' => $users,
            'tickets' => $tickets,
        ]);
    }

    /**
     * Updates an existing Problem model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        
        // Получаем списки для выпадающих списков
        $categories = Category::find()->active()->all();
        $statuses = Status::find()->active()->all();
        $authors = Author::find()->active()->all();
        $users = User::find()->where(['status' => User::STATUS_ACTIVE])->all();
        $tickets = Ticket::find()->orderBy(['id' => SORT_DESC])->limit(100)->all();

        if ($model->load(Yii::$app->request->post())) {
            // Сохраняем привязанные заявки
            $ticketIds = Yii::$app->request->post('Problem')['ticket_ids'] ?? [];
            
            if ($model->save()) {
                // Сохраняем связи с заявками
                $this->saveProblemTickets($model, $ticketIds);
                
                Yii::$app->session->setFlash('success', Yii::t('app', 'Problem successfully updated.'));
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
            'categories' => $categories,
            'statuses' => $statuses,
            'authors' => $authors,
            'users' => $users,
            'tickets' => $tickets,
        ]);
    }

    /**
     * Deletes an existing Problem model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        Yii::$app->session->setFlash('success', Yii::t('app', 'Problem successfully deleted.'));
        return $this->redirect(['index']);
    }

    /**
     * Finds the Problem model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Problem the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Problem::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }

    /**
     * Сохраняет связи проблемы с заявками
     * @param Problem $model
     * @param array $ticketIds
     * @return void
     */
    protected function saveProblemTickets($model, $ticketIds)
    {
        // Удаляем старые связи
        \yii\db\Query::createCommand()
            ->delete('{{%problem_tickets}}', ['problem_id' => $model->id])
            ->execute();

        // Добавляем новые связи
        if (!empty($ticketIds) && is_array($ticketIds)) {
            foreach ($ticketIds as $ticketId) {
                \yii\db\Query::createCommand()
                    ->insert('{{%problem_tickets}}', [
                        'problem_id' => $model->id,
                        'ticket_id' => $ticketId,
                        'created_at' => date('Y-m-d H:i:s'),
                    ])
                    ->execute();
            }
        }
    }
}
