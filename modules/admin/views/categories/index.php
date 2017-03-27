<?php

use kartik\grid\ActionColumn;
use kartik\grid\FormulaColumn;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yiicms\components\core\Url;
use yiicms\components\core\widgets\Alert;
use yiicms\models\content\Category;
use yiicms\modules\admin\components\adminlte\GridView;

/**
 * @var $this \yii\web\View
 * @var \yii\data\ActiveDataProvider $dataProvider Data provider
 * @var $model Category
 */

$gridConfig = [
    'id' => 'categories-grid',
    'dataProvider' => $dataProvider,
    'filterModel' => $model,
    'responsive' => false,
    'hover' => true,
    'columns' => [
        [
            'attribute' => 'title',
            'class' => FormulaColumn::class,
            'format' => 'raw',
            'value' => function ($model) {
                /** @var Category $model */
                return '<div style="margin-left: ' . ($model->levelNod - 1) * 30 . 'px"><a target="_blank" data-noajax="1" href="' .
                    Url::to(['/admin/category/' . $model->slug]) . '">' . $model['title'] . '</a></div>';
            },
            'label' => $model->getAttributeLabel('title'),
            'contentOptions' => ['style' => 'width: 40%;'],
        ],
        [
            'attribute' => 'createdAt',
            'class' => FormulaColumn::class,
            'value' => function ($model) {
                return \Yii::$app->formatter->asDatetime($model['createdAt']);
            },
            'label' => $model->getAttributeLabel('createdAt'),
        ],
        'weight',
        'slug',
        'keywords',
        [
            'class' => ActionColumn::class,
            'template' => '<li>{add}</li><li>{edit}</li><li>{del}</li><li>{del-with-childs}</li><li><hr></li><li>{perm}</li>',
            'dropdown' => true,
            'dropdownOptions' => ['class' => 'pull-right'],
            'dropdownButton' => ['class' => 'btn btn-primary', 'label' => '<i class="fa fa-cogs"></i>'],
            'buttons' => [
                'add' => function ($url, $model) {
                    return Html::a(
                        '<i class="fa fa-plus"></i> ' . \Yii::t('yiicms', 'Добавить дочернюю'),
                        Url::toWithNewReturn(['/admin/categories/add', 'parentId' => $model['categoryId']]),
                        ['data-pjax' => 0, 'title' => \Yii::t('yiicms', 'Создать дочернюю категорию в этой категории')]
                    );
                },
                'edit' => function ($url, $model) {
                    return Html::a(
                        '<i class="fa fa-pencil"></i> ' . \Yii::t('yiicms', 'Изменить'),
                        Url::toWithNewReturn(['/admin/categories/edit', 'categoryId' => $model['categoryId']]),
                        ['data-pjax' => 0, 'title' => \Yii::t('yiicms', 'Изменить данные категории')]
                    );
                },
                'del' => function ($url, $model) {
                    return Html::a(
                        '<i class="fa fa-trash-o"></i> ' . \Yii::t('yiicms', 'Удалить'),
                        Url::toWithNewReturn(['/admin/categories/delete', 'categoryId' => $model['categoryId'], 'removeChild' => 0]),
                        [
                            'data-method' => 'post',
                            'data-confirm' => \Yii::t('yiicms', 'Удалить категории?'),
                            'title' => \Yii::t('yiicms', 'Удалить только эту категорию'),
                        ]
                    );
                },
                'del-with-childs' => function ($url, $model) {
                    return Html::a(
                        '<i class="fa fa-trash"></i> ' . \Yii::t('yiicms', 'Удалить с дочерними'),
                        Url::toWithNewReturn(['/admin/categories/delete', 'categoryId' => $model['categoryId'], 'removeChild' => 1]),
                        [
                            'data-method' => 'post',
                            'data-confirm' => \Yii::t('yiicms', 'Удалить категорию и все ее дочерние подкатегории?'),
                            'title' => \Yii::t('yiicms', 'Удалить эту категории и все ее дочерние подкатегории'),
                        ]
                    );
                },
                'perm' => function ($url, $model) {
                    return Html::a(
                        '<i class="fa fa-file-o"></i> ' . \Yii::t('yiicms', 'Разрешения'),
                        Url::toWithNewReturn([
                            '/admin/categories/permission',
                            'categoryId' => $model['categoryId'],
                        ]),
                        ['data-pjax' => 0, 'title' => \Yii::t('yiicms', 'Разрешения на доступ к этой категории для групп пользователей')]
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
            '<i class="fa fa-plus"> </i> ' . \Yii::t('yiicms', 'Создать корневую категорию'),
            Url::toWithNewReturn(['/admin/categories/add']),
            ['class' => 'btn pull-right btn-primary']
        ) ?>
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