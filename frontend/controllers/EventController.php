<?php

namespace frontend\controllers;

use Yii;
use common\models\Event;
use common\models\EventSearch;
use common\models\Subtype;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * EventController implements the CRUD actions for Event model.
 */
class EventController extends Controller
{

    public $layout = 'authorized';
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ]
        ];
    }

    /**
     * Lists all Event models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new EventSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Lists actual events
     * @return string
     */
    public function actionActual()
    {
        $dataProvider = Event::find()->active();

        return $this->render('index', [
            'dataProvider' => new ActiveDataProvider([
                'query' => $dataProvider
            ]),
            'counts' => [
                'applied' => $this->getAppliedCount(),
                'own' => $this->getOwnCount(),
                'archived' => $this->getArchivedCount()
            ]
        ]);
    }

    /**
     * Lists archived events
     * @return string
     */
    public function actionArchived() {
        $dataProvider = Event::find()->active(false);

        return $this->render('index', [
            'dataProvider' => new ActiveDataProvider([
                'query' => $dataProvider
            ])
        ]);
    }

    /**
     * Lists user`s own events
     * @return string
     */
    public function actionOwn() {
        $dataProvider = Event::find()->byUserId(Yii::$app->user->id);

        return $this->render('index', [
            'dataProvider' => new ActiveDataProvider([
                'query' => $dataProvider
            ])
        ]);
    }

    /**
     * Lists events user had applied to
     * @return string
     */
    public function actionApplied() {
        $dataProvider = Event::find()->withReportFromUser(Yii::$app->user->id);

        return $this->render('index', [
            'dataProvider' => new ActiveDataProvider([
                'query' => $dataProvider
            ])
        ]);
    }

    /**
     * Count events user has applied to
     * @return int
     */
    public function getAppliedCount() {
        return Event::find()->withReportFromUser(Yii::$app->user->id)->count();
    }

    /**
     * Count events user has created
     * @return int
     */
    public function getOwnCount() {
        return Event::find()->byUserId(Yii::$app->user->id)->count();
    }

    /**
     * Count non-actual events
     * @return int
     */
    public function getArchivedCount() {
        return Event::find()->active(false)->count();
    }

    /**
     * Displays a single Event model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Event model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Event();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Event model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Event model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Event model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Event the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Event::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
