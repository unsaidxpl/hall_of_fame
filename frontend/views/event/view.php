<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Event */

$this->title = $model->humanType();
$this->params['breadcrumbs'][] = ['label' => 'Events', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?= Html::a('<i class="glyphicon glyphicon-menu-left"></i>&nbsp;Назад', Yii::$app->request->referrer) ?>
<div class="event-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?php if ($model->user_id == Yii::$app->user->id) { ?>
            <?= Html::a('Редактировать', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Вы уверены, что хотите удалить?',
                    'method' => 'post',
                ],
            ]) ?>
        <?php } ?>
    </p>

    <div class="row">
        <div class="col-md-6 col-xs-12 text-center">
            <?php if (file_exists($model->getPhotoPath())) { ?>
                <?= Yii::$app->thumbnail->img($model->getPhotoPath(), [
                    'thumbnail' => [
                        'width' => 500,
                        'height' => 500,
                    ]
                ], [
                    'class' => 'img img-responsive'
                ]); ?>
            <?php } ?>
        </div>
        <div class="col-md-6 col-xs-12">
            <h4><i class="glyphicon glyphicon-user"
                   title="ФИО почетного гражданина, которому посвящено мероприятие"></i>
                <?= $model->person_name ?>
            </h4>
            <h4><?= $model->humanType() ?></h4>
            <p><i class="glyphicon glyphicon-map-marker" title="Город"></i> <?= $model->city ?></p>
            <p><i class="glyphicon glyphicon-home" title="Место"></i> <?= $model->place ?></p>
            <p><i class="glyphicon glyphicon-calendar" title="Дата проведения"></i>&nbsp;
                <?= Yii::$app->formatter->asDate($model->date, 'd MMMM y года, HH:mm') ?></p>
            <?php if (!$model->isMine() && !$model->hasMyReport()) { ?>
                <div class="col-xs-6 col-sm-3">
                    <?= Html::a('Подать отчёт', [
                        'report/create', 'event_id' => $model->id
                    ], [
                        'class' => 'btn btn-primary'
                    ]) ?>
                </div>
            <?php } ?>
        </div>
    </div>
    <p>
        <?= $model->content ?>
    </p>
    <?php if (!$model->isMine() && !$model->hasMyReport()) { ?>
        <div class="col-xs-6 col-sm-3">
            <?= Html::a('Подать отчёт', [
                'report/create', 'event_id' => $model->id
            ], [
                'class' => 'btn btn-primary'
            ]) ?>
        </div>
    <?php } else if ($model->getMyReport()) {
        if ($model->getMyReport()->status == 'dismissed') { ?>
            <div class="text-danger"><i class="glyphicon glyphicon-time"></i>&nbsp;Ваш отчет отклонен</div>
        <?php } else if ($model->getMyReport()->status == 'pending') { ?>
            <div class="text-info"><i class="glyphicon glyphicon-time"></i>&nbsp;Ваш отчет находится на рассмотрении</div>
        <?php }
    }?>
    <?php if (Yii::$app->user->identity->isAdmin) { ?>
        <?php if ($model->status == 'pending') { ?>
            <?= Html::a('Опубликовать', ['publish', 'id' => $model->id], [
                'class' => 'btn btn-success'
            ]) ?>
            <?= Html::a('Отклонить', ['dismiss', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Вы уверены, что хотите отклонить мероприятие?',
                    'method' => 'post',
                ],
            ]) ?>
        <?php }
        if ($model->status == 'published') { ?>
            <?= Html::a('Снять с публикации', ['publish', 'id' => $model->id, 'reverse' => true], [
                'class' => 'btn btn-default',
                'data' => [
                    'confirm' => 'Вы уверены, что хотите скрыть мероприятие?',
                    'method' => 'post',
                ],
            ]) ?>
        <?php } ?>
    <?php }?>

</div>

