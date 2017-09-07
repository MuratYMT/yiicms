<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 22.01.2016
 * Time: 10:11
 */

use kartik\grid\ActionColumn;
use kartik\grid\FormulaColumn;
use yii\widgets\Pjax;
use yiicms\components\core\widgets\Alert;
use yiicms\components\YiiCms;
use yiicms\modules\admin\components\adminlte\GridView;
use yii\helpers\Html;
use yiicms\components\core\Url;
use yiicms\models\core\Menus;
use yiicms\models\core\VisibleForPathInfo;

/**
 * @var $this \yii\web\View
 * @var $model Menus
 * @var $dataProvider \yii\data\ActiveDataProvider
 */

$gridConfig = [
    'dataProvider' => $dataProvider,
    'filterModel' => $model,
    'responsive' => false,
    'hover' => true,
    'columns' => [
        [
            'attribute' => 'title',
            'class' => FormulaColumn::class,
            'format' => 'raw',
            'value' => function ($model) {
                /** @var Menus $model */
                $result = [];
                foreach ($model->titleM as $lang => $title) {
                    $result[] = $lang . ' --> ' . $title;
                }
                return '<div style="margin-left: ' . (($model->levelNod - 1) * 30) . 'px">' . implode('<br>',
                        $result) . '</div>';
            },
        ],
        [
            'attribute' => 'subTitle',
            'class' => FormulaColumn::class,
            'format' => 'raw',
            'value' => function ($model) {
                /** @var Menus $model */
                $result = [];
                foreach ($model->subTitleM as $lang => $title) {
                    $result[] = $lang . ' --> ' . $title;
                }
                return implode('<br>', $result);
            },
        ],
        [
            'attribute' => 'icon',
            'class' => FormulaColumn::class,
            'format' => 'raw',
            'value' => function ($model) {
                /** @var Menus $model */
                return empty($model->icon) ? '' : '<h4><i class="fa ' . $model->icon . ' fa-3"></i></h4>';
            },
        ],
        'link',
        'weight',
        [
            'attribute' => 'pathInfoVisibleOrder',
            'class' => FormulaColumn::class,
            'format' => 'raw',
            'value' => function ($model) {
                return YiiCms::$app->blockService->visibleOrderLabels($model['pathInfoVisibleOrder']);
            },
        ],
        [
            'class' => ActionColumn::class,
            'template' => '<li>{add-child}</li><li>{edit}</li><li>{del}</li><li>{del-with-childs}</li><li>
                <hr></li><li>{role-visible}</li><li>{role-visible-as-this}</li><li>{pathinfo-visible}</li>',
            'dropdownOptions' => ['class' => 'pull-right'],
            'dropdown' => true,
            'dropdownButton' => ['class' => 'btn btn-primary', 'label' => '<i class="fa fa-cogs"></i>'],
            'buttons' => [
                'add-child' => function ($url, $model) {
                    return Html::a(
                        '<i class="fa fa-plus"></i> ' . \Yii::t('yiicms', 'Добавить дочерний'),
                        Url::toWithNewReturn(['/admin/menus/add', 'parentId' => $model['menuId']]),
                        ['data-pjax' => 0, 'title' => \Yii::t('yiicms', 'Добавить дочерний пункт меню')]
                    );
                },
                'edit' => function ($url, $model) {
                    return Html::a(
                        '<i class="fa fa-pencil"></i> ' . \Yii::t('yiicms', 'Изменить'),
                        Url::toWithNewReturn(['/admin/menus/edit', 'menuId' => $model['menuId']]),
                        ['data-pjax' => 0, 'title' => \Yii::t('yiicms', 'Изменить этот пункт меню')]
                    );
                },
                'del' => function ($url, $model) {
                    return Html::a(
                        '<i class="fa fa-trash"></i> ' . \Yii::t('yiicms', 'Удалить'),
                        Url::toWithNewReturn(['/admin/menus/del-menu', 'menuId' => $model['menuId']]),
                        [
                            'data-method' => 'post',
                            'data-confirm' => \Yii::t('yiicms', 'Удалить?'),
                            'title' => \Yii::t('yiicms', 'Удалить этот пункт меню'),
                        ]
                    );
                },
                'del-with-childs' => function ($url, $model) {
                    return Html::a(
                        '<i class="fa fa-trash"></i> ' . \Yii::t('yiicms', 'Удалить с дочерними'),
                        Url::toWithNewReturn([
                            '/admin/menus/del-menu',
                            'menuId' => $model['menuId'],
                            'removeChild' => 1
                        ]),
                        [
                            'title' => \Yii::t('yiicms', 'Удалить этот пункт меню и все его дочерние пункты'),
                            'data-confirm' => \Yii::t('yiicms', 'Удалить этот пункт меню и все его дочерние пункты?'),
                            'data-method' => 'post',
                        ]
                    );
                },
                'role-visible' => function ($url, $model) {
                    return Html::a(
                        '<i class="fa fa-users"></i> ' . \Yii::t('yiicms', 'Видимость для ролей'),
                        Url::toWithNewReturn(['/admin/menus/role-visible', 'menuId' => $model['menuId']]),
                        [
                            'data-pjax' => 0,
                            'title' => \Yii::t('yiicms', 'Видимость пункта меню для ролей пользователей')
                        ]
                    );
                },
                'role-visible-as-this' => function ($url, $model) {
                    return Html::a(
                        '<i class="fa fa-refresh"></i> ' . \Yii::t('yiicms', 'Заменить видимость у дочерних как тут'),
                        Url::toWithNewReturn(['/admin/menus/children-visible-as-this', 'menuId' => $model['menuId']]),
                        [
                            'data-method' => 'post',
                            'data-confirm' => \Yii::t('yiicms', 'Заменить видимость у дочерних пунктов меню?'),
                            'title' => \Yii::t(
                                'yiicms',
                                'Заменить видимость для ролей у дочерних пунктов меню как у этого пункта меню'
                            ),
                        ]
                    );
                },
                'pathinfo-visible' => function ($url, $model) {
                    return Html::a(
                        '<i class="fa fa-files-o"></i> ' . \Yii::t('yiicms', 'Видимость на страницах'),
                        Url::toWithNewReturn(['/admin/menus/path-info-visible', 'menuId' => $model['menuId']]),
                        [
                            'data-pjax' => 0,
                            'title' => \Yii::t('yiicms', 'Видимость пункта меню на различных страницах сайта')
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
            '<i class="fa fa-plus"> </i> ' . \Yii::t('yiicms', 'Создать корневой пункт меню'),
            Url::toWithNewReturn(['/admin/menus/add']),
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

