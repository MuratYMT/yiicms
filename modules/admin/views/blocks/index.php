<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 11.03.2015
 * Time: 8:35
 */

use kartik\grid\ActionColumn;
use kartik\grid\BooleanColumn;
use kartik\grid\FormulaColumn;
use yii\bootstrap\Dropdown;
use yii\helpers\Html;
use yiicms\components\core\blocks\BlockWidget;
use yiicms\components\core\Url;
use yiicms\components\YiiCms;
use yiicms\models\core\Blocks;
use yiicms\models\core\constants\VisibleForPathInfoConst;
use yiicms\models\core\VisibleForPathInfo;
use yiicms\modules\admin\components\adminlte\GridView;
use yiicms\modules\admin\models\blocks\BlocksSearch;

/**
 * @var $this \yii\web\View
 * @var $model BlocksSearch
 * @var $dataProvider \yii\data\ActiveDataProvider
 */

$gridConfig = [
    'id' => 'blocks-grid',
    'dataProvider' => $dataProvider,
    'filterModel' => $model,
    'responsive' => false,
    'hover' => true,
    'columns' => [
        [
            'attribute' => 'title',
            'class' => FormulaColumn::class,
            'format' => 'html',
            'value' => function ($model) {
                /** @var Blocks $model */
                $result = [];
                foreach ($model->titleM as $lang => $title) {
                    $result[] = $lang . ' --> ' . $title;
                }
                return implode('<br>', $result);
            },
        ],
        'description',
        [
            'attribute' => 'position',
            'filter' => array_combine(
                YiiCms::$app->blockService->availablePosition(),
                YiiCms::$app->blockService->availablePosition()
            ),
        ],
        'weight',
        [
            'attribute' => 'activy',
            'filter' => [0 => Yii::t('yiicms', 'Не активный'), 1 => Yii::t('yiicms', 'Активный')],
            'class' => BooleanColumn::class,
        ],
        [
            'attribute' => 'pathInfoVisibleOrder',
            'class' => FormulaColumn::class,
            'filter' => array_combine(
                VisibleForPathInfoConst::VISIBLE_ARRAY,
                YiiCms::$app->blockService->visibleOrderLabels()
            ),
            'value' => function ($model) {
                return YiiCms::$app->blockService->visibleOrderLabels($model['pathInfoVisibleOrder']);
            },
        ],
        [
            'class' => ActionColumn::class,
            'template' => '<li>{edit}</li><li>{del}</li><li>
                <hr></li><li>{role-visible}</li><li>{pathinfo-visible}</li>',
            'dropdownOptions' => ['class' => 'pull-right'],
            'dropdown' => true,
            'dropdownButton' => ['class' => 'btn btn-primary', 'label' => '<i class="fa fa-cogs"></i>'],
            'buttons' => [
                'edit' => function ($url, $model) {
                    return Html::a(
                        '<i class="fa fa-pencil"></i> ' . \Yii::t('yiicms', 'Изменить'),
                        Url::toWithNewReturn(['/admin/blocks/edit', 'blockId' => $model['blockId']]),
                        ['title' => \Yii::t('yiicms', 'Изменить блок'), 'data-pjax' => 0]
                    );
                },
                'del' => function ($url, $model) {
                    return Html::a(
                        '<i class="fa fa-trash"></i> ' . \Yii::t('yiicms', 'Удалить'),
                        Url::toWithNewReturn(['/admin/blocks/del-block', 'blockId' => $model['blockId']]),
                        [
                            'data-method' => 'post',
                            'data-confirm' => \Yii::t('yiicms', 'Удалить?'),
                            'title' => \Yii::t('yiicms', 'Удалить этот блок')
                        ]
                    );
                },
                'role-visible' => function ($url, $model) {
                    return Html::a(
                        '<i class="fa fa-users"></i> ' . \Yii::t('yiicms', 'Видимость для ролей'),
                        Url::toWithNewReturn(['/admin/blocks/role-visible', 'blockId' => $model['blockId']]),
                        ['title' => \Yii::t('yiicms', 'Видимость блоков для ролей пользователей'), 'data-pjax' => 0]
                    );
                },
                'pathinfo-visible' => function ($url, $model) {
                    return Html::a(
                        '<i class="fa fa-files-o"></i> ' . \Yii::t('yiicms', 'Видимость на страницах'),
                        Url::toWithNewReturn(['/admin/blocks/path-info-visible', 'blockId' => $model['blockId']]),
                        [
                            'title' => \Yii::t('yiicms', 'Видимость блоков на различных страницах сайта'),
                            'data-pjax' => 0
                        ]
                    );
                },
            ],
        ],
    ],
];

$dropdown = [];
foreach (YiiCms::$app->blockService->getAvailableBlocksClass() as $class) {
    /** @var BlockWidget $obj */
    $obj = new $class;
    $dropdown[$class] = [
        'label' => $obj->title,
        'url' => Url::toWithNewReturn(['/admin/blocks/add', 'contentClass' => $class]),
    ];
}

?>

<div class="row button-row">
    <div class="col-md-12 col-sm-12">
        <div class="dropdown pull-right">
            <?= Html::button(
                '<i class="fa fa-plus"> </i> ' . \Yii::t('yiicms', 'Создать'),
                [
                    'class' => 'btn btn-primary',
                    'data-toggle' => 'dropdown',
                    'aria-haspopup' => 'true',
                    'aria-expanded' => 'false',
                ]
            ); ?>
            <?= Dropdown::widget(['items' => $dropdown]) ?>
        </div>
    </div>
</div>
<div class="row">
    <div class="form-group col-md-12 col-sm-12">
        <?= GridView::widget($gridConfig) ?>
    </div>
</div>