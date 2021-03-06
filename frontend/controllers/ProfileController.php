<?php
/**
 * Created by PhpStorm.
 * User: ekz
 * Date: 9/6/17
 * Time: 7:41 PM
 */

namespace frontend\controllers;

use dektrium\user\controllers\ProfileController as BaseController;
use yii\web\UploadedFile;
use yii\helpers\ArrayHelper;
use yii\filters\VerbFilter;
use yii\helpers\Json;


class ProfileController extends BaseController
{

    /** @inheritdoc */
    public function behaviors()
    {
        $behaviors = [
            'access' => [
                'rules' => [
                    ['allow' => true, 'actions' => ['upload-avatar'], 'roles' => ['@']]
                ]
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'upload-avatar' => ['post'],
                ],
            ],
        ];
        return ArrayHelper::merge($behaviors, parent::behaviors());
    }

    /**
     * Upload user avatar
     */
    public function actionUploadAvatar()
    {
        $model = \Yii::$app->user->identity->profile;
        $image = UploadedFile::getInstance($model, 'image');
        if ($image) {
            if ($model->photo) {
                $this->deletePhoto($model->photo);
            }
            $names = explode(".", $image->name);
            $ext = end($names);
            $model->photo = \Yii::$app->security->generateRandomString() . ".{$ext}";
            $result = $image->saveAs(UPLOAD_PATH . $model->photo) && $model->save(false);
            if ($result) {
                $url = \Yii::$app->thumbnail->url(UPLOAD_PATH . $model->photo, [
                    'thumbnail' => [
                        'width' => 200,
                        'height' => 200,
                    ]
                ]);
            }
        }
        $response = $result ? ['url' => $url] : ['error' => 'Ошибка при загрузке'];
        echo Json::encode($response);
    }

    /**
     * Delete user`s avatar
     * @param $photo
     * @return bool
     */
    protected function deletePhoto($photo) {
        $baseUrl = UPLOAD_PATH . $photo;
        if (file_exists($baseUrl)) {
            return unlink($baseUrl);
        }
        return false;
    }
}
