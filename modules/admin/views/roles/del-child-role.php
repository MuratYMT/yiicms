<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 11.03.2015
 * Time: 8:35
 */
use kartik\grid\ActionColumn;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yiicms\components\core\Url;
use yiicms\components\core\widgets\Alert;
use yiicms\components\core\widgets\CloseButton;
use yii\web\View;
use yiicms\modules\admin\components\adminlte\GridView;
use yiicms\modules\admin\models\roles\RolesSearch;

/**
 * @var View $this
 * @var ActiveDataProvider $dataProvider Data provider
 * @var RolesSearch $model
 * @var string $parentRoleName
 */

$gridId = 'roles-grid';
$gridConfig = [
    'id' => $gridId,
    'dataProvider' => $dataProvider,
    'filterModel' => $model,
    'responsive' => false,
    'hover' => true,
    'columns' => [
        'name',
        'description',
        [
            'class' => ActionColumn::class,
            'template' => '{del}',
            'buttons' => [
                'del' => function ($url, $model) use ($parentRoleName) {
                    return Html::a(
                        '<i class="fa fa-minus"></i>',
                        Url::toWithCurrentReturn(['/admin/roles/del-child-role', 'parentRole' => $parentRoleName, 'childRole' => $model['name']]),
                        [
                            'data-confirm' => Yii::t('yiicms', 'Удалить из списка дочерних?'),
                            'data-method' => 'post',
                            'title' => \Yii::t('yiicms', 'Удалить из списка дочерних'),
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
