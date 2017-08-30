<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\SubtypeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Подтипы мероприятий';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="subtype-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Создать подтип', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'name',
            'type',
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
