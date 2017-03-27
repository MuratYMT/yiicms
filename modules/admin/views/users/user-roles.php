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
use yii\helpers\Html;
use yii\widgets\Pjax;
use yiicms\components\core\Url;
use yiicms\components\core\widgets\Alert;
use yiicms\components\core\widgets\CloseButton;
use yiicms\modules\admin\components\adminlte\GridView;
use yiicms\modules\admin\models\users\RolesSearch;

/**
 * @var $this \yii\web\View
 * @var $model RolesSearch
 * @var \yii\data\ActiveDataProvider $dataProvider Data provider
 * @var $userId int
 */

$gridConfig = [
    'id' => 'user-roles-grid',
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
            'contentOptions' => ['style' => 'width: 60%;'],
        ],
        'description',
        [
            'attribute' => 'assign',
            'class' => BooleanColumn::class,
        ],
        [
            'class' => ActionColumn::class,
            'template' => '<li>{assign}</li><li>{revoke}</li>',
            'dropdownOptions' => ['class' => 'pull-right'],
            'dropdown' => true,
            'dropdownButton' => ['class' => 'btn btn-primary', 'label' => '<i class="fa fa-cogs"></i>'],
            'buttons' => [
                'assign' => function ($url, $model) use ($userId) {
                    return Html::a(
                        '<i class="fa fa-plus"></i> ' . \Yii::t('yiicms', 'Назначить'),
                        Url::toWithNewReturn(['/admin/users/role-assign', 'userId' => $userId, 'roleName' => $model['name']]),
                        ['data-pjax' => 1, 'data-method' => 'post', 'title' => \Yii::t('yiicms', 'Назначить эту роль пользователю')]
                    );
                },
                'revoke' => function ($url, $model) use ($userId) {
                    return Html::a(
                        '<i class="fa fa-minus"></i> ' . \Yii::t('yiicms', 'Отозвать'),
                        Url::toWithNewReturn(['/admin/users/role-revoke', 'userId' => $userId, 'roleName' => $model['name']]),
                        [
                            'data-confirm' => Yii::t('yiicms', 'Отозвать у пользователя выбранные роли?'),
                            'data-pjax' => 1,
                            'data-method' => 'post',
                            'title' => \Yii::t('yiicms', 'Отозвать эту роль у пользователя'),
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
    </div>
</div>
<div class="row">
    <div class="col-md-12 col-sm-12">
        <?php Pjax::begin() ?>
        <?= Alert::widget() ?>
        <?= GridView::widget($gridConfig); ?>
        <?php Pjax::end() ?>
    </div>
</div>
