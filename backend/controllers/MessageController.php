<?php

namespace backend\controllers;

use Yii;
use common\models\Message;
use common\models\MessageSearch;
use yii\web\NotFoundHttpException;

/**
 * MessageController implements the CRUD actions for Message model.
 */
class MessageController extends BackendController
{


    /**
     * Lists all Message models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new MessageSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Message model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        if ($model->state == 'pending') {
            $this->setAsRead($model);
        }
        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * @param $model Message
     * @return mixed
     */
    private function setAsRead($model) {
        $model->state = 'read';
        return $model->save();
    }

    /**
     * Deletes an existing Message model.
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
     * Finds the Message model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Message the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Message::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
