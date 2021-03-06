<?php

/**
 * @todo move to partial views
 */

use yii\helpers\Html;
use yii\web\View;
use kartik\dialog\Dialog;
use kartik\file\FileInput;
use yii\widgets\ActiveForm;
use common\models\Event;
use common\models\Report;
use common\models\User;

$counts = [
    'active' => Event::find()->published()->active()->count(),
    'own' => Event::find()->byUserId(Yii::$app->user->id)->count(),
    'applied' => Event::find()->published()->appliedByUser(Yii::$app->user->id)->distinct()->count(),
    'archived' => Event::find()->published()->active(false)->count(),
    'events-pending' => Event::find()->pending()->byUserId(Yii::$app->user->id)->count(),
    'reports-pending' => Report::find()->pending()->byUserId(Yii::$app->user->id)->count(),
    'my-reports' => Report::find()->byUserId(Yii::$app->user->id)->count()
];
$profileModel = \Yii::$app->user->identity->profile;
$this->beginContent('@frontend/views/layouts/main.php');
if (!Yii::$app->user->isGuest) {
    ?>
    <div class="profile-header row top-20">
        <div class="col-md-7 col-xs-12">
            <div class="panel panel-default">
                <div class="panel-heading">

                    <?= Html::encode(Yii::$app->user->identity->profile->name) ?>


                </div>
                <div class="panel-body">
                    <div class="col-xs-12 col-sm-6">
                        <?php $form = ActiveForm::begin([
                            'options' => ['enctype'=>'multipart/form-data'],
                            'action' => '/profile/upload-avatar'
                        ]);

                        $avatarUrl = Yii::$app->user->identity->profile->getPhotoPath();
                        $thumbUrl = ($avatarUrl && file_exists($avatarUrl)) ? Yii::$app->thumbnail->url($avatarUrl, [
                            'thumbnail' => [
                                'width' => 200,
                                'height' => 200,
                            ]
                        ]) : '/images/default_avatar.jpg'; ?>

                        <?= Html::img($thumbUrl, ['class' => 'img img-responsive avatar-thumb rounded', 'id' => 'my-avatar']) ?>

                        <?= $form->field($profileModel, 'image', [
                            'options' => [
                                'style' => 'display: none',
                                'id' => 'avatar-upload'
                            ]
                        ])->widget(FileInput::className(), [
                            'pluginOptions' => [
                                'initialPreview'=>[
                                    $thumbUrl
                                ],
                                'initialPreviewAsData' => true,
                                'overwriteInitial' => true,
                                'showRemove' => false,
                                'showUpload' => false,
                                'showCaption' => false,
                                'uploadUrl' => '/user/profile/upload-avatar',
                                'browseLabel' => 'Выбрать файл'
                            ]
                        ])->label(false) ?>
                        <?php ActiveForm::end(); ?>

                    </div>
                    <div class="col-xs-12 col-sm-6">
                        <div class="bottom-10">
                            <div class="btn-group full-width right-10 info-group">
                                <label class="btn btn-success col-xs-9 ellipsis">
                                    Баллы, которые я заработал
                                </label>
                                <label class="btn btn-default col-xs-3">
                                    <i class="glyphicon glyphicon-star-empty"></i>
                                    <?= User::findIdentity(\Yii::$app->user->id)->getScore() ?>
                                </label>
                            </div>
                            <div class="btn-group full-width right-10 info-group">
                                <label class="btn btn-info col-xs-9 ellipsis">
                                    Мероприятий на рассмотрении
                                </label>
                                <label class="btn btn-default col-xs-3">
                                    <i class="glyphicon glyphicon-calendar"></i>
                                    <?= $counts['events-pending'] ?>
                                </label>
                            </div>
                            <div class="btn-group full-width info-group">
                                <label class="btn btn-warning col-xs-9 ellipsis">
                                    Отчетов на рассмотрении
                                </label>
                                <label class="btn btn-default col-xs-3">
                                    <i class="glyphicon glyphicon-list-alt"></i>
                                    <?= $counts['reports-pending'] ?>
                                </label>
                            </div>
                        </div>
                        <p>
                            <i class="glyphicon glyphicon-envelope" title="E-mail"></i> <?= Yii::$app->user->identity->email ?>
                        </p>
                        <?php if (\Yii::$app->user->identity->profile->city) { ?>
                            <p>
                                <i class="glyphicon glyphicon-map-marker" title="Город"></i> <?= Yii::$app->user->identity->profile->city ?>
                            </p>
                        <?php } ?>
                        <?php if (\Yii::$app->user->identity->profile->phone) { ?>
                            <p>
                                <i class="glyphicon glyphicon-phone" title="Телефон"></i> <?= Yii::$app->user->identity->profile->phone ?>
                            </p>
                        <?php } ?>

                            <?= Html::a('Изменить информацию о себе', ['/user/settings/profile'], ['class' => 'profile-link']) ?><br>
                            <?= Html::a('Изменить фотографию', '#', ['class' => 'profile-link', 'id' => 'upload-mode']) ?>

                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-5 col-xs-12">
            <div class="row">
                <div class="col-xs-12">
                    <?= Html::a('<i class="glyphicon glyphicon-lock"></i>&nbsp;Запросить новый пароль', ['/user/recovery/resend-password'], [
                        'data' => [
                            'confirm' => 'На ваш адрес E-mail будет отправлен новый автоматически сгенерированный пароль.' .
                                'Ваш текущий пароль станет недейтвителен. Вы уверены?',
                            'method' => 'post',
                        ],
                        'class' => 'btn btn-default pull-left right-10'
                    ]) ?>
                    <?= Html::beginForm(['/site/logout'], 'post')
                    . Html::submitButton(
                        '<i class="glyphicon glyphicon-log-out"></i>&nbsp;Выйти',
                        ['class' => 'btn btn-default logout pull-left']
                    )
                    . Html::endForm()
                    ?>
                </div>
            </div>
            <div class="clearfix top-20">
                <ul class="list-group">
                    <a href="/event/actual" class="list-group-item">
                        Предстоящие мероприятия: <span class="badge"><?= $counts['active'] ?></span>
                    </a>
                    <a href="/event/own" class="list-group-item">
                        Мероприятия, которые я запланировал: <span class="badge"><?= $counts['own'] ?></span>
                    </a>
                    <a href="/event/applied" class="list-group-item">
                        Мероприятия, в которых я участвую: <span class="badge"><?= $counts['applied'] ?></span>
                    </a>
                    <a href="/event/archived" class="list-group-item">
                        Завершенные мероприятия: <span class="badge"><?= $counts['archived'] ?></span>
                    </a>
                    <a href="/report/own" class="list-group-item">
                        Мои отчеты: <span class="badge"><?= $counts['my-reports'] ?></span>
                    </a>
                </ul>
                <p>
                    <?= Html::a('Запланировать новое мероприятие', ['create'], ['class' => 'btn btn-success']) ?>
                </p>
            </div>
        </div>
    </div>
    <hr>
    <?= Dialog::widget([
        'options' => [
            'title' => 'Подтвердите действие'
        ]
    ]); ?>

    <?= $content ?>
    <?php
}
$this->endContent(); ?>

