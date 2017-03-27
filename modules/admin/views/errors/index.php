<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 30.12.2016
 * Time: 10:11
 */
use kartik\grid\ExpandRowColumn;
use kartik\grid\FormulaColumn;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yiicms\components\core\Url;
use yii\web\View;
use yiicms\components\core\widgets\Alert;
use yiicms\models\core\Log;
use yiicms\modules\admin\components\adminlte\GridView;

/**
 * @var View $this
 * @var \yiicms\modules\admin\models\ErrorsSearch $model
 * @var ActiveDataProvider $dataProvider Data provider
 */

$gridConfig = [
    'id' => 'errors-grid',
    'dataProvider' => $dataProvider,
    'filterModel' => $model,
    'responsive' => true,
    'hover' => true,
    'columns' => [
        [
            'class' => ExpandRowColumn::class,
            'value' => function () {
                return GridView::ROW_COLLAPSED;
            },
            'detail' => function ($model) {
                /** @var Log $model */
                return '<pre>' . $model->message . '</pre>';
            },
        ],
        [
            'attribute' => 'log_time',
            'class' => FormulaColumn::class,
            'format' => 'raw',
            'value' => function ($model) {
                /**@var $model Log */
                return Html::a(
                    \Yii::$app->formatter->asDatetime(round($model->log_time)),
                    Url::toWithNewReturn(['/admin/errors/view', 'id' => $model['id']]),
                    ['data-click' => 1]
                );
            },
        ],
        'level',
        'category',
        'prefix',
    ],
];

?>
<div class="row">
    <div class="col-md-12 col-sm-12">
        <?php Pjax::begin() ?>
        <?php Alert::widget() ?>
        <?= GridView::widget($gridConfig); ?>
        <?php Pjax::end() ?>
    </div>
</div>