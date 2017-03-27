<?php

use kartik\grid\ActionColumn;
use kartik\grid\BooleanColumn;
use kartik\grid\FormulaColumn;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yiicms\components\core\Url;
use yiicms\components\core\widgets\Alert;
use yiicms\components\core\widgets\CloseButton;
use yiicms\models\content\CategoryPermission;
use yiicms\modules\admin\components\adminlte\GridView;
use yiicms\modules\admin\models\categories\CategoryPermissionSearch;

/**
 * @var $this \yii\web\View
 * @var \yii\data\ArrayDataProvider $dataProvider Data provider
 * @var $categoryId integer
 * @var $model CategoryPermissionSearch
 */

$actionColumn = [
    'class' => ActionColumn::class,
    'template' => '{edit}',
    'dropdownOptions' => ['class' => 'pull-left'],
    'buttons' => [
        'edit' => function ($url, $model) use ($categoryId) {
            return Html::a(
                '<i class="fa fa-pencil-square-o fa-2"> </i> ',
                Url::toWithNewReturn([
                    '/admin/categories/permission-edit',
                    'categoryId' => $categoryId,
                    'roleName' => $model['roleName'],
                ]),
                ['data-pjax' => 0, 'title' => \Yii::t('yiicms', 'Изменить разрешения для этой роли в этой категории')]
            );
        },
    ],
];
$columns = [$actionColumn];

$columns[] = [
    'attribute' => 'roleName',
    'label' => \Yii::t('yiicms', 'Роль'),
    'class' => FormulaColumn::class,
    'format' => 'raw',
    'value' => function ($model) {
        return '<div style="margin-left: ' . (($model['level'] - 1) * 30) . 'px">' . $model['roleName'] . '</div>';
    },
];

foreach (CategoryPermission::$permissions as $perm) {
    $columns[] = [
        'attribute' => $perm,
        'label' => CategoryPermission::permissionLabels($perm),
        'class' => BooleanColumn::class,
    ];
}
$columns[] = [
    'attribute' => 'roleName2',
    'label' => \Yii::t('yiicms', 'Роль'),
    'class' => FormulaColumn::class,
    'format' => 'raw',
    'value' => function ($model) {
        return '<div style="margin-left: ' . (($model['level'] - 1) * 30) . 'px">' . $model['roleName'] . '</div>';
    },
];

$columns[] = $actionColumn;

$gridConfig = [
    'id' => 'category-permission-grid',
    'dataProvider' => $dataProvider,
    'filterModel' => $model,
    'responsive' => true,
    'hover' => true,
    'columns' => $columns,
];
?>
<div class="row button-row">
    <div class="col-md-12 col-sm-12">
        <?= CloseButton::widget(); ?>
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