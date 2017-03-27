<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 11.03.2015
 * Time: 8:35
 * @var \yii\data\ActiveDataProvider $dataProvider Data provider
 */
use kartik\grid\FormulaColumn;
use yii\data\ActiveDataProvider;
use yii\grid\ActionColumn;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yiicms\components\core\Url;
use yiicms\components\core\widgets\Alert;
use yiicms\components\core\widgets\CloseButton;
use yiicms\modules\admin\components\adminlte\GridView;
use yiicms\modules\admin\models\roles\RolesSearch;

/**
 * @var \yii\web\View $this
 * @var RolesSearch $model
 * @var ActiveDataProvider $dataProvider
 * @var string $parentRoleName
 */

$gridConfig = [
    'id' => 'child-role-select-grid',
    'dataProvider' => $dataProvider,
    'filterModel' => $model,
    'responsive' => false,
    'hover' => true,
    'columns' => [
        [
            'attribute' => 'name',
            'class' => FormulaColumn::class,
            'format' => 'raw',
            'value' => function ($model) {
                return '<div style="margin-left: ' . ($model['level'] - 1) * 30 . 'px">' . $model['name'] . '</div>';
            },
            'label' => \Yii::t('yiicms', 'Название роли'),
        ],
        'description',
        [
            'class' => ActionColumn::class,
            'template' => '{add}',
            'buttons' => [
                'add' => function ($url, $model) use ($parentRoleName) {
                    return Html::a(
                        '<i class="fa fa-plus"></i>',
                        Url::toWithCurrentReturn(['/admin/roles/add-child-role', 'parentRole' => $parentRoleName, 'childRole' => $model['name']]),
                        ['data-method' => 'post', 'title' => \Yii::t('yiicms', 'Добавить дочерней')]
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
