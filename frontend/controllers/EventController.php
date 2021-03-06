<?php

namespace frontend\controllers;

use Yii;
use common\models\Event;
use common\models\EventSearch;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use dektrium\user\filters\AccessRule;

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
            ],
            'access' => [
                'class' => AccessControl::className(),
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'rules' => [
                    [
                        'actions' => ['memory-demo', 'legacy-demo'],
                        'allow' => true,
                        'roles' => ['?', '@']
                    ],
                    [
                        'actions' => ['create', 'update', 'index', 'own', 'actual', 'applied', 'archived', 'view', 'delete'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['publish', 'dismiss'],
                        'allow' => true,
                        'roles' => ['admin']
                    ]

                ],
            ],
        ];
    }

    /**
     * Lists actual events
     * @return string
     */
    public function actionActual()
    {
        return $this->renderIndex('Предстоящие мероприятия',
            Event::find()->published()->active());
    }

    /**
     * Lists archived events
     * @return string
     */
    public function actionArchived() {
        return $this->renderIndex('Завершенные мероприятия',
            Event::find()->published()->active(false));
    }

    /**
     * Lists user`s own events
     * @return string
     */
    public function actionOwn() {
        return $this->renderIndex('Мероприятия, которые я запланировал',
            Event::find()->byUserId(Yii::$app->user->id));
    }

    /**
     * Lists events user had applied to
     * @return string
     */
    public function actionApplied() {
        return $this->renderIndex('Мероприятия, в которых я участвую',
            Event::find()->published()->appliedByUser(Yii::$app->user->id));
    }

    /**
     * @param $pageTitle
     * @param $query
     * @return string
     */
    private function renderIndex($pageTitle, $query) {
        $searchModel = new EventSearch();
        $query = $searchModel->addFilterParams($query, Yii::$app->request->queryParams);
        $request = Yii::$app->request;
        $types = array_keys(Event::HUMAN_TYPES);
        $providers = [];
        $pagerParams = $_GET;

        foreach ($types as $type) {
            $pagerParams['type'] = $type;
            $typedQuery = clone $query;
            $providers[$type . 'DataProvider'] = new ActiveDataProvider([
                'query' => $typedQuery->byType($type)->orderBy('event.date DESC'),
                'pagination' => [
                    'pageSize' => 5,
                    'params' => $pagerParams
                ]
            ]);
        }
        $data = array_merge(['pageTitle' => $pageTitle, 'searchModel' => $searchModel], $providers);
        if ($request->isAjax && in_array($request->get('type'), ['legacy', 'memory'])) {
            return $this->renderPartial("_{$request->get('type')}", $data);
        } else {
            return $this->render('index', $data);
        }
    }

    /**
     * Displays a single Event model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $this->layout = 'main';
        $model = $this->findModel($id);

        if ($model->deleted_at != null) {
            throw new NotFoundHttpException('Страница не существует.');
        }

        if ($model->status == 'pending' && !$model->isMine()) {
            $this->redirect(['/account']);
        }

        return $this->render('view', [
            'model' => $model,
            'reportsDataProvider' => new ActiveDataProvider([
                'query' => $model->getReports()->published(),
                'pagination' => [
                    'pageSize' => 5
                ]
            ])
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
        $model->scenario = 'create';

        if ($model->load(Yii::$app->request->post())) {
            $this->saveImage($model);
            if (Yii::$app->user->identity->isAdmin) {
                $model->status = 'published';
                $message = 'Мероприятие успешно опубликовано';
            } else {
                $message = 'Мероприятие отправлено на модерацию';
            }
            if ($model->save()) {
                \Yii::$app->getSession()->setFlash('success', $message);
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }
        return $this->render('create', [
            'model' => $model,
        ]);
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
        $model->scenario = 'update';

        if ($model->status == 'published' || !$model->isMine()) {
            $this->redirect(['/account']);
        }

        if ($model->load(Yii::$app->request->post())) {
            $this->saveImage($model);
            if ($model->save()) {
                \Yii::$app->getSession()->setFlash('success', 'Данные мероприятия успешно обновлены');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }
        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * @param $model
     */
    private function saveImage($model) {
        $image = UploadedFile::getInstance($model, 'image');
        if ($image) {
            $names = explode(".", $image->name);
            $ext = end($names);
            $model->photo = Yii::$app->security->generateRandomString() . ".{$ext}";
            $image->saveAs(UPLOAD_PATH . $model->photo);
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
        $model = $this->findModel($id);
        if (!$model->isMine() || (!Yii::$app->user->identity->isAdmin && $model->status == 'published')) {
            $this->redirect(['/account']);
        }
        $model->updateAttributes(['deleted_at' => time()]);
        $model->afterDelete();
        return $this->redirect(['/account']);
    }

    /**
     * Publish event
     * @param $id
     * @param $reverse
     * @return \yii\web\Response
     */
    public function actionPublish($id, $reverse = false) {
        $newStatus = $reverse ? 'pending' : 'published';
        $model = $this->findModel($id);
        if (!$model->isMine()) {
            $this->redirect(['/account']);
        }
        $model->status = $newStatus;
        if (!$model->save()) {
            \Yii::$app->getSession()->setFlash('error', 'Возникла ошибка при обновлении записи');
        }
        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Dismiss pending event
     * @param $id
     * @return \yii\web\Response
     */
    public function actionDismiss($id) {
        $model = $this->findModel($id);
        $model->status = 'dismissed';
        if (!$model->save()) {
            \Yii::$app->getSession()->setFlash('error', 'Возникла ошибка при обновлении записи');
        }
        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Show demo page for Memory events
     * @return mixed
     */
    public function actionMemoryDemo() {
        $this->layout = 'static';
        $searchModel = new EventSearch();
        $query = $searchModel->addFilterParams(Event::find()->published()->byType('memory'), Yii::$app->request->queryParams);
        return $this->render('demo', [
            'type' => 'memory',
            'searchModel' => $searchModel,
            'dataProvider' => new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => 6
                ]
            ])
        ]);
    }

    /**
     * Show demo page for Legacy events
     * @return mixed
     */
    public function actionLegacyDemo() {
        $this->layout = 'static';
        $searchModel = new EventSearch();
        $query = $searchModel->addFilterParams(Event::find()->published()->byType('legacy'), Yii::$app->request->queryParams);
        return $this->render('demo', [
            'type' => 'legacy',
            'searchModel' => $searchModel,
            'dataProvider' => new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => 6
                ]
            ])
        ]);
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
            throw new NotFoundHttpException('Страница не существует.');
        }
    }
}
