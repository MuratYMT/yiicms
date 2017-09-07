<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 21.01.2016
 * Time: 8:49
 */

use kartik\grid\ActionColumn;
use kartik\grid\FormulaColumn;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yiicms\components\core\Url;
use yiicms\components\core\widgets\Alert;
use yiicms\components\core\widgets\CloseButton;
use yii\web\View;
use yiicms\components\YiiCms;
use yiicms\models\core\BlocksVisibleForPathInfo;
use yiicms\modules\admin\components\adminlte\GridView;
use yiicms\modules\admin\models\blocks\BlocksVisibleForPathInfoSearch;

/**
 * @var View $this
 * @var ArrayDataProvider $dataProvider
 * @var integer $blockId
 * @var BlocksVisibleForPathInfoSearch $model
 */

$dataProvider->pagination->pageSize = 10;

$gridConfig = [
    'id' => 'block-pathinfo-permission-grid',
    'dataProvider' => $dataProvider,
    'filterModel' => $model,
    'responsive' => false,
    'hover' => true,
    'columns' => [
        [
            'class' => FormulaColumn::class,
            'attribute' => 'rule',
            'value' => function ($model) {
                /**@var $model BlocksVisibleForPathInfoSearch */
                return YiiCms::$app->blockService->ruleLabels($model['rule']);
            },
        ],
        'template',
        [
            'class' => ActionColumn::class,
            'template' => '<li>{edit}</li><li>{remove}</li>',
            'dropdown' => true,
            'dropdownOptions' => ['class' => 'pull-right'],
            'dropdownButton' => ['class' => 'btn btn-primary', 'label' => '<i class="fa fa-cogs"></i>'],
            'buttons' => [
                'edit' => function ($url, $model) use ($blockId) {
                    return Html::a(
                        '<i class="fa fa-pencil"></i> ' . \Yii::t('yiicms', 'Изменить'),
                        Url::toWithNewReturn(['/admin/blocks/path-info-visible-edit', 'permId' => $model['permId']]),
                        ['title' => \Yii::t('yiicms', 'Изменить это правило'), 'data-pjax' => 0]
                    );
                },
                'remove' => function ($url, $model) use ($blockId) {
                    return Html::a(
                        '<i class="fa fa-trash-o"></i> ' . \Yii::t('yiicms', 'Удалить'),
                        Url::toWithNewReturn([
                            '/admin/blocks/path-info-visible-del',
                            'permId' => $model['permId'],
                        ]),
                        [
                            'title' => \Yii::t('yiicms', 'Удалить это правило'),
                            'data-confirm' => \Yii::t('yiicms', 'Удалить это правило?'),
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
    <div class="col-md-12">
        <?= CloseButton::widget(); ?>
        <?= Html::a(
            '<i class="fa fa-plus"> </i> ' . \Yii::t('yiicms', 'Создать правило'),
            Url::toWithNewReturn(['/admin/blocks/path-info-visible-add', 'blockId' => $blockId]),
            ['class' => 'btn pull-right btn-primary']
        ); ?>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <?php Pjax::begin() ?>
        <?php Alert::widget() ?>
        <?= GridView::widget($gridConfig); ?>
        <?php Pjax::end() ?>
    </div>
</div>