<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 01.02.2016
 * Time: 9:55
 */

use kartik\grid\ActionColumn;
use kartik\grid\FormulaColumn;
use yii\widgets\Pjax;
use yiicms\components\core\widgets\Alert;
use yiicms\modules\admin\components\adminlte\GridView;
use yiicms\components\core\Url;
use yii\web\View;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yiicms\models\core\Crontabs;

/**
 * @var View $this
 * @var \yiicms\modules\admin\models\CrontabsSearch $model
 * @var ActiveDataProvider $dataProvider Data provider
 */

$gridConfig = [
    'dataProvider' => $dataProvider,
    'filterModel' => $model,
    'responsive' => false,
    'hover' => true,
    'columns' => [
        'runTime',
        'descript',
        [
            'attribute' => 'lastRunStart',
            'class' => FormulaColumn::class,
            'format' => 'raw',
            'value' => function ($model) {
                /**@var $model Crontabs */
                return \Yii::$app->formatter->asDatetime($model->lastRunStart);
            },
        ],
        [
            'attribute' => 'lastRunStop',
            'class' => FormulaColumn::class,
            'format' => 'raw',
            'value' => function ($model) {
                /**@var $model Crontabs */
                return \Yii::$app->formatter->asDatetime($model->lastRunStop);
            },
        ],
        [
            'class' => ActionColumn::class,
            'template' => '<li>{edit}</li><li>{del}</li><li><hr></li><li>{run}</li>',
            'dropdownOptions' => ['class' => 'pull-right'],
            'dropdown' => true,
            'dropdownButton' => ['class' => 'btn btn-primary', 'label' => '<i class="fa fa-cogs"></i>'],
            'buttons' => [
                'edit' => function ($url, $model) {
                    return Html::a(
                        '<i class="fa fa-pencil"></i> ' . \Yii::t('yiicms', 'Изменить'),
                        Url::toWithNewReturn(['/admin/crontab/edit', 'jobClass' => $model['jobClass']]),
                        ['data-pjax' => 0, 'title' => \Yii::t('yiicms', 'Изменить задание')]
                    );
                },
                'del' => function ($url, $model) {
                    return Html::a(
                        '<i class="fa fa-trash"></i> ' . \Yii::t('yiicms', 'Удалить'),
                        Url::toWithNewReturn(['/admin/crontab/del', 'jobClass' => $model['jobClass']]),
                        [
                            'data-confirm' => \Yii::t('yiicms', 'Удалить?'),
                            'title' => \Yii::t('yiicms', 'Удалить это задание?'),
                            'data-method' => 'post',
                        ]
                    );
                },
                'run' => function ($url, $model) {
                    return Html::a(
                        '<i class="fa fa-trash"></i> ' . \Yii::t('yiicms', 'Запустить'),
                        Url::toWithNewReturn(['/admin/crontab/run', 'jobClass' => $model['jobClass']]),
                        [
                            'data-confirm' => \Yii::t('yiicms', 'Запустить?'),
                            'title' => \Yii::t('yiicms', 'Запустить выполнение задачи'),
                            'data-method' => 'post',
                        ]
                    );
                },
            ],
        ],
    ],
];

?>
<div class="row button-row">
    <div class="col-md-12 col-sm-12">
        <?= Html::a(
            '<i class="fa fa-plus"> </i> ' . \Yii::t('yiicms', 'Добавить задание'),
            Url::toWithNewReturn(['/admin/crontab/add']),
            ['class' => 'btn pull-right btn-primary']
        ); ?>
    </div>
</div>

<div class="row">
    <div class="col-md-12 col-sm-12">
        <?php Pjax::begin() ?>
        <?php Alert::widget() ?>
        <?= GridView::widget($gridConfig); ?>
        <?php Pjax::end() ?>
    </div>
</div>