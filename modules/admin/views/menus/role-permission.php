<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 25.01.2016
 * Time: 9:59
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
use yiicms\modules\admin\models\menus\MenusVisibleForRoleSearch;

/**
 * @var View $this
 * @var ArrayDataProvider $dataProvider
 * @var integer $menuId
 * @var MenusVisibleForRoleSearch $model
 */

$dataProvider->pagination->pageSize = 10;

$gridConfig = [
    'id' => 'role-permission-grid',
    'dataProvider' => $dataProvider,
    'filterModel' => $model,
    'responsive' => false,
    'hover' => true,
    'columns' => [
        'roleName',
        [
            'class' => BooleanColumn::class,
            'attribute' => 'visible',
        ],
        [
            'class' => ActionColumn::class,
            'template' => '<li>{grant}</li><li>{grant-recursive}</li><li>{revoke}</li><li>{revoke-recursive}</li>',
            'dropdown' => true,
            'dropdownOptions' => ['class' => 'pull-right'],
            'dropdownButton' => ['class' => 'btn btn-primary', 'label' => '<i class="fa fa-cogs"></i>'],
            'buttons' => [
                'grant' => function ($url, $model) use ($menuId) {
                    return Html::a(
                        '<i class="fa fa-plus"></i> ' . \Yii::t('yiicms', 'Разрешить'),
                        Url::toWithNewReturn(['/admin/menus/role-visiable-grant', 'menuId' => $menuId, 'roleName' => $model['roleName']]),
                        ['data-method' => 'post', 'title' => \Yii::t('yiicms', 'Разрешить пользователям этой роли видеть пункт меню')]
                    );
                },
                'grant-recursive' => function ($url, $model) use ($menuId) {
                    return Html::a(
                        '<i class="fa fa-plus-square"></i> ' . \Yii::t('yiicms', 'Разрешить рекурсивно'),
                        Url::toWithNewReturn([
                            '/admin/menus/role-visiable-grant',
                            'menuId' => $menuId,
                            'roleName' => $model['roleName'],
                            'recursive' => true,
                        ]),
                        [
                            'data-method' => 'post',
                            'title' => \Yii::t('yiicms', 'Разрешить пользователям этой роли видеть пункт меню и все его подпункты'),
                        ]
                    );
                },
                'revoke' => function ($url, $model) use ($menuId) {
                    return Html::a(
                        '<i class="fa fa-minus"></i> ' . \Yii::t('yiicms', 'Отменить'),
                        Url::toWithNewReturn(['/admin/menus/role-visiable-revoke', 'menuId' => $menuId, 'roleName' => $model['roleName']]),
                        ['data-method' => 'post', 'title' => \Yii::t('yiicms', 'Отменить видимость пункта меню пользователям этой роли')]
                    );
                },
                'revoke-recursive' => function ($url, $model) use ($menuId) {
                    return Html::a(
                        '<i class="fa fa-minus-square"></i> ' . \Yii::t('yiicms', 'Отменить рекурсивно'),
                        Url::toWithNewReturn([
                            '/admin/menus/role-visiable-revoke',
                            'menuId' => $menuId,
                            'roleName' => $model['roleName'],
                            'recursive' => true,
                        ]),
                        [
                            'data-method' => 'post',
                            'title' => \Yii::t('yiicms', 'Отменить видимость пункта меню и всех его подпунктов пользователям этой роли'),
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
        <?php Alert::widget() ?>
        <?= GridView::widget($gridConfig) ?>
        <?php Pjax::end() ?>
    </div>
</div>