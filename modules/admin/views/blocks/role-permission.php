<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 21.01.2016
 * Time: 8:49
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
use yiicms\modules\admin\models\blocks\BlocksVisibleForRoleSearch;

/**
 * @var View $this
 * @var ArrayDataProvider $dataProvider
 * @var integer $blockId
 * @var BlocksVisibleForRoleSearch $model
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
            'template' => '<li>{grant}</li><li>{revoke}</li>',
            'dropdown' => true,
            'dropdownOptions' => ['class' => 'pull-right'],
            'dropdownButton' => ['class' => 'btn btn-primary', 'label' => '<i class="fa fa-cogs"></i>'],
            'buttons' => [
                'grant' => function ($url, $model) use ($blockId) {
                    return Html::a(
                        '<i class="fa fa-plus"></i> ' . \Yii::t('yiicms', 'Разрешить'),
                        Url::toWithNewReturn([
                            '/admin/blocks/role-visible-grant',
                            'blockId' => $blockId,
                            'roleName' => $model['roleName'],
                        ]),
                        ['data-method' => 'post', 'data-pjax' => 1, 'title' => \Yii::t('yiicms', 'Разрешить пользователям этой роли видеть блок')]
                    );
                },
                'revoke' => function ($url, $model) use ($blockId) {
                    return Html::a(
                        '<i class="fa fa-minus"></i> ' . \Yii::t('yiicms', 'Отменить'),
                        Url::toWithNewReturn([
                            '/admin/blocks/role-visible-revoke',
                            'blockId' => $blockId,
                            'roleName' => $model['roleName'],
                        ]),
                        ['data-method' => 'post', 'data-pjax' => 1, 'title' => \Yii::t('yiicms', 'Отменить видимость блока пользователям этой роли')]
                    );
                },
            ],
        ],
    ],
];

?>
<div class="row button-row">
    <div class="col-md-12">
        <?= CloseButton::widget(); ?>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <?php Pjax::begin() ?>
        <?php Alert::widget() ?>
        <?= GridView::widget($gridConfig); ?>
        <?php Pjax::end() ?>
    </div>
</div>