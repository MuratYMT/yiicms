<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 11.03.2015
 * Time: 8:35
 */
use yii\widgets\Pjax;
use yiicms\components\core\widgets\Alert;
use yiicms\components\core\widgets\CloseButton;
use yiicms\modules\admin\models\users\PermissionSearch;
use yiicms\modules\admin\components\adminlte\GridView;

/**
 * @var $this \yii\web\View
 * @var $model PermissionSearch
 * @var \yii\data\ActiveDataProvider $dataProvider Data provider
 */

$gridConfig = [
    'id' => 'user-permission-grid',
    'dataProvider' => $dataProvider,
    'filterModel' => $model,
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
            'attribute' => 'role',
            'label' => \Yii::t('yiicms', 'Где назначено'),
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
        <?= GridView::widget($gridConfig); ?>
        <?php Pjax::end() ?>
    </div>
</div>
