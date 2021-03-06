<?php
/**
 * Created by PhpStorm.
 * User: unsai
 * Date: 22.08.2017
 * Time: 22:05
 */

namespace frontend\controllers;

use dektrium\user\controllers\SettingsController as BaseController;
use common\models\User;
use dektrium\user\models\Profile;


class SettingsController extends BaseController
{
    /**
     * Shows profile settings form.
     *
     * @return string|\yii\web\Response
     */
    public function actionProfile()
    {
        $model = $this->finder->findProfileById(\Yii::$app->user->identity->getId());
        $userModel = $this->finder->findUserById(\Yii::$app->user->identity->getId());

        if ($model == null) {
            $model = \Yii::createObject(Profile::className());
            $model->link('user', \Yii::$app->user->identity);
        }

        $event = $this->getProfileEvent($model);

        $this->performAjaxValidation($model);

        $this->trigger(self::EVENT_BEFORE_PROFILE_UPDATE, $event);
        if ($model->load(\Yii::$app->request->post())) {
            $email = \Yii::$app->request->post('User')['email'];
            if ($email) {
                $userModel->email = $email;
                $userModel->save();
            }
            if ($model->save()) {
                \Yii::$app->getSession()->setFlash('success', \Yii::t('user', 'Your profile has been updated'));
                $this->trigger(self::EVENT_AFTER_PROFILE_UPDATE, $event);
                return $this->refresh();
            }
        }

        return $this->render('profile', [
            'model' => $model,
            'userModel' => $userModel
        ]);
    }
}
