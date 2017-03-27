<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 11.03.2015
 * Time: 8:35
 * @var \yii\data\ActiveDataProvider $dataProvider Data provider
 */
use kartik\grid\FormulaColumn;
use yii\data\ArrayDataProvider;
use yii\widgets\Pjax;
use yiicms\components\core\widgets\Alert;
use yiicms\components\core\widgets\CloseButton;
use yiicms\modules\admin\components\adminlte\GridView;
use yiicms\modules\admin\models\roles\PermissionSearch;

/**
 * @var $this \yii\web\View
 * @var $model PermissionSearch
 * @var bool $showManageButton
 * @var string $roleName
 * @var $dataProvider ArrayDataProvider
 */

$gridConfig = [
    'dataProvider' => $dataProvider,
    'filterModel' => null,
    'responsive' => false,
    'hover' => true,
    'columns' => [
        [
            'attribute' => 'name',
            'label' => \Yii::t('yiicms', 'Разрешение'),
        ],
        [
            'attribute' => 'description',
            'label' => \Yii::t('yiicms', 'Описание'),
        ],
        [
            'class' => FormulaColumn::class,
            'attribute' => 'role',
            'label' => \Yii::t('yiicms', 'Где назначено'),
            'value' => function ($model) {
                return is_array($model['role']) ? implode(', ', $model['role']) : $model['role'];
            },
        ],
    ],
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
        <?= GridView::widget($gridConfig) ?>
        <?php Pjax::end() ?>
    </div>
</div>
