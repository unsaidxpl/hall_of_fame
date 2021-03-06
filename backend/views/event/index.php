<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\grid\ActionColumn;

/* @var $this yii\web\View */
/* @var $searchModel common\models\EventSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Мероприятия';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="event-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',

            [
                'attribute' => 'type',
                'content' => function($data) {
                    return $data->humanType();
                }
            ],
            'date',
            'city',
            [
                'attribute' => 'subtype_id',
                'label' => 'Подтип',
                'content' => function($data) {
                    return $data->subtype->name;
                }
            ],
            [
                'attribute' => 'status',
                'content' => function($data) {
                    return $data->humanState();
                }
            ],
            [
                'attribute' => 'user',
                'label' => 'Автор',
                'content' => function($data){
                    return $data->user->profile->name;
                },
            ],

            ['class' => ActionColumn::className(), 'template' => '{view} {delete}' ],
        ],
    ]); ?>
</div>
