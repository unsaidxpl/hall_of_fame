<?php
namespace frontend\models;

use dektrium\user\models\User as BaseUser;
use common\models\Score;

class User extends BaseUser
{
    public function init() {
        $this->on(self::BEFORE_REGISTER, function() {
            $this->username = $this->email;
        });

        parent::init();
    }

    public function rules() {
        $rules = parent::rules();
        unset($rules['usernameRequired']);
        return $rules;
    }

    /**
     * Получить число баллов
     * @return \yii\db\ActiveQuery
     */
    public function getScore() {
        return $this->hasMany(Score::className(), ['user_id' => 'id'])->sum('amount');
    }
}
