<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 11.03.2015
 * Time: 8:35
 */
use kartik\grid\ActionColumn;
use kartik\grid\FormulaColumn;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yiicms\components\core\Url;
use yiicms\components\core\widgets\Alert;
use yii\web\View;
use yiicms\modules\admin\components\adminlte\GridView;
use yiicms\modules\admin\models\roles\RolesSearch;

/**
 * @var $this View
 * @var $model RolesSearch
 * @var ActiveDataProvider $dataProvider Data provider
 */

$gridConfig = [
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
            'label' => Yii::t('yiicms', 'Роль'),
        ],
        [
            'attribute' => 'description',
            'label' => Yii::t('yiicms', 'Описание'),
        ],
        [
            'attribute' => 'createdAt',
            'class' => FormulaColumn::class,
            'value' => function ($model) {
                return \Yii::$app->formatter->asDatetime($model['createdAt']);
            },
            'label' => Yii::t('yiicms', 'Дата создания'),
        ],
        [
            'class' => ActionColumn::class,
            'template' => '<li>{edit-role}</li><li>{del-role}</li><li><hr></li>' .
                '<li>{add-child-role}</li><li>{del-child-role}</li><li><hr></li>' .
                '<li>{permission}</li><li>{add-permission}</li></li><li>{all-permission}</li>',
            'dropdown' => true,
            'dropdownOptions' => ['class' => 'pull-right'],
            'dropdownButton' => ['class' => 'btn btn-primary', 'label' => '<i class="fa fa-cogs"></i>'],
            'buttons' => [
                'edit-role' => function ($url, $model) {
                    return Html::a(
                        '<i class="fa fa-pencil"></i> ' . \Yii::t('yiicms', 'Изменить'),
                        Url::toWithNewReturn(['/admin/roles/edit-role', 'roleName' => $model['name']]),
                        ['data-pjax' => 0, 'title' => \Yii::t('yiicms', 'Изменить эту роль')]
                    );
                },
                'del-role' => function ($url, $model) {
                    return Html::a(
                        '<i class="fa fa-trash"></i> ' . \Yii::t('yiicms', 'Удалить'),
                        Url::toWithNewReturn(['/admin/roles/del-role', 'roleName' => $model['name']]),
                        [
                            'data-confirm' => \Yii::t('yiicms', 'Удалить?'),
                            'title' => \Yii::t('yiicms', 'Удалить эту роль'),
                            'data-method' => 'post',
                        ]
                    );
                },
                'add-child-role' => function ($url, $model) {
                    return Html::a(
                        '<i class="fa fa-plus"></i> ' . \Yii::t('yiicms', 'Добавить дочернюю'),
                        Url::toWithNewReturn(['/admin/roles/add-child-role', 'parentRole' => $model['name']]),
                        ['data-pjax' => 0, 'title' => \Yii::t('yiicms', 'Установить одну из существующих ролей как дочернюю для этой роли')]
                    );
                },
                'del-child-role' => function ($url, $model) {
                    return Html::a(
                        '<i class="fa fa-trash-o"></i> ' . \Yii::t('yiicms', 'Удалить дочерние'),
                        Url::toWithNewReturn(['/admin/roles/del-child-role', 'parentRole' => $model['name']]),
                        [
                            'data-pjax' => 0,
                            'title' => \Yii::t('yiicms', 'Открыть окно для выбора ролей который надо удалить из списка дочерних этой роли'),
                        ]
                    );
                },
                'permission' => function ($url, $model) {
                    return Html::a(
                        '<i class="fa fa-file-o"></i> ' . \Yii::t('yiicms', 'Назначенные разрешения'),
                        Url::toWithNewReturn(['/admin/roles/role-permission', 'roleName' => $model['name']]),
                        ['data-pjax' => 0, 'title' => \Yii::t('yiicms', 'Разрешения назначенные этой роли')]
                    );
                },
                'all-permission' => function ($url, $model) {
                    return Html::a(
                        '<i class="fa fa-files-o"></i> ' . \Yii::t('yiicms', 'Результирующие разрешения'),
                        Url::toWithNewReturn(['/admin/roles/role-all-permission', 'roleName' => $model['name']]),
                        ['data-pjax' => 0, 'title' => \Yii::t('yiicms', 'Все разрешения (включая разрешения назначенные в дочерних ролях)')]
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
            '<i class="fa fa-plus"> </i> ' . \Yii::t('yiicms', 'Создать роль'),
            Url::toWithNewReturn(['/admin/roles/add-role']),
            ['data-click' => 1, 'class' => 'btn pull-right btn-primary']
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
