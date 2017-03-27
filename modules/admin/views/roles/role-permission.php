<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 11.03.2015
 * Time: 8:35
 * @var \yii\data\ActiveDataProvider $dataProvider Data provider
 */
use kartik\grid\ActionColumn;
use kartik\grid\BooleanColumn;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yiicms\components\core\Url;
use yiicms\components\core\widgets\Alert;
use yiicms\components\core\widgets\CloseButton;
use yii\web\View;
use yiicms\modules\admin\components\adminlte\GridView;
use yiicms\modules\admin\models\roles\PermissionSearch;

/**
 * @var $this View
 * @var $model PermissionSearch
 * @var string $roleName
 * @var $dataProvider ArrayDataProvider
 */

$gridConfig = [
    'id'=>'role-permission-grid',
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
            'class' => BooleanColumn::class,
            'attribute' => 'assign',
            'label' => \Yii::t('yiicms', 'Назначено'),
        ],
        [
            'class' => ActionColumn::class,
            'template' => '<li>{assign}</li><li>{revoke}</li>',
            'dropdown' => true,
            'dropdownOptions' => ['class' => 'pull-right'],
            'dropdownButton' => ['class' => 'btn btn-primary', 'label' => '<i class="fa fa-cogs"></i>'],
            'buttons' => [
                'assign' => function ($url, $model) use ($roleName) {
                    return Html::a(
                        '<i class="fa fa-plus"></i> ' . \Yii::t('yiicms', 'Назначить'),
                        Url::toWithCurrentReturn(['/admin/roles/assign', 'roleName' => $roleName, 'permissionName' => $model['name']]),
                        [
                            'title' => \Yii::t('yiicms', 'Назначить это разрешиение роли'),
                            'data-method' => 'post',
                            'data-pjax' => 1,
                        ]
                    );
                },
                'revoke' => function ($url, $model) use ($roleName) {
                    return Html::a(
                        '<i class="fa fa-minus"></i> ' . \Yii::t('yiicms', 'Отозвать'),
                        Url::toWithCurrentReturn(['/admin/roles/revoke', 'roleName' => $roleName, 'permissionName' => $model['name']]),
                        [
                            'title' => \Yii::t('yiicms', 'Отозвать это разрешение у роли'),
                            'data-method' => 'post',
                            'data-pjax' => 1,
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
        <?= CloseButton::widget(); ?>
    </div>
</div>
<div class="row">
    <div class="col-md-12 col-sm-12">
        <?php Pjax::begin() ?>
        <?= Alert::widget() ?>
        <?= GridView::widget($gridConfig) ?>
        <?php Pjax::end() ?>
    </div>
</div>
