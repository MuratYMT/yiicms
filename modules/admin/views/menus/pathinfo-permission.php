<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 25.01.2016
 * Time: 11:42
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
use yiicms\models\core\MenusVisibleForPathInfo;
use yiicms\modules\admin\components\adminlte\GridView;
use yiicms\modules\admin\models\menus\MenusVisibleForPathInfoSearch;

/**
 * @var View $this
 * @var ArrayDataProvider $dataProvider
 * @var integer $menuId
 * @var MenusVisibleForPathInfoSearch $model
 */

$dataProvider->pagination->pageSize = 10;

$gridConfig = [
    'id' => 'menu-pathinfo-permission-grid',
    'dataProvider' => $dataProvider,
    'filterModel' => $model,
    'responsive' => false,
    'hover' => true,
    'columns' => [
        [
            'class' => FormulaColumn::class,
            'attribute' => 'rule',
            'value' => function ($model) {
                /**@var $modelMenusVisibleForPathInfo */
                return YiiCms::$app->blockService->ruleLabels($model->rule);
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
                'edit' => function ($url, $model) use ($menuId) {
                    return Html::a(
                        '<i class="fa fa-pencil"></i> ' . \Yii::t('yiicms', 'Изменить'),
                        Url::toWithNewReturn(['/admin/menus/path-info-visible-edit', 'permId' => $model['permId']]),
                        ['data-pjax' => 0, 'title' => \Yii::t('yiicms', 'Изменить это правило')]
                    );
                },
                'remove' => function ($url, $model) use ($menuId) {
                    return Html::a(
                        '<i class="fa fa-trash-o"></i> ' . \Yii::t('yiicms', 'Удалить'),
                        Url::toWithNewReturn(['/admin/menus/path-info-visible-del', 'permId' => $model['permId']]),
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
    <div class="col-md-12 col-sm-12">
        <?= CloseButton::widget() ?>
        <?= Html::a(
            '<i class="fa fa-plus"> </i> ' . \Yii::t('yiicms', 'Создать правило'),
            Url::toWithNewReturn(['/admin/menus/path-info-visible-add', 'menuId' => $menuId]),
            ['class' => 'btn pull-right btn-primary']
        ); ?>
    </div>
</div>
<div class="row">
    <div class="col-md-12 col-sm-12">
        <?php Pjax::begin() ?>
        <?php Alert::widget() ?>
        <?= GridView::widget($gridConfig) ?>
        <?php Pjax::end() ?>
    </div>
</div>
