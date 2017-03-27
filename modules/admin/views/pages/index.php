<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 01.09.2015
 * Time: 10:19
 */

use yii\widgets\Pjax;
use yiicms\components\core\Url;
use kartik\grid\ActionColumn;
use kartik\grid\BooleanColumn;
use kartik\grid\FormulaColumn;
use yiicms\components\core\widgets\Alert;
use yiicms\modules\admin\components\adminlte\GridView;
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $searchModel yiicms\modules\admin\models\pages\PagesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$gridConfig = [
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => [
        [
            'class' => ActionColumn::class,
            'template' => '{edit} {delete}',
            'dropdownOptions' => ['class' => 'pull-left'],
            'dropdownButton' => ['class' => 'btn btn-primary', 'label' => '<i class="fa fa-cogs"></i>'],
            'buttons' => [
                'edit' => function ($url, $model) {
                    return Html::a(
                        '<i class="fa fa-pencil-square-o fa-2"> </i> ',
                        Url::toWithNewReturn(['/admin/pages/edit', 'pageId' => $model['pageId']]),
                        ['title' => \Yii::t('yiicms', 'Изменить страницу'), 'data-pjax' => 0]
                    );
                },
                'delete' => function ($url, $model) {
                    return Html::a(
                        '<i class="fa fa-trash fa-2"> </i> ',
                        Url::toWithNewReturn(['/admin/pages/delete', 'pageId' => $model['pageId']]),
                        [
                            'title' => \Yii::t('yiicms', 'Удалить страницу'),
                            'data-method' => 'post',
                            'data-confirm' => \Yii::t('yiicms', 'Удалить страницу?'),
                            'data-pjax' => 1,
                        ]
                    );
                },
            ],
        ],
        [
            'attribute' => 'title',
            'class' => FormulaColumn::class,
            'format' => 'raw',
            'value' => function ($model) {
                /** @var \yiicms\models\content\Page $model */
                return Html::a(
                    $model->title,
                    Url::to(['/page/' . $model['slugFull']]),
                    ['title' => \Yii::t('yiicms', 'Просмотр страницы'), 'target' => '_blank']
                );
            },
        ],
        'ownerLogin',
        [
            'attribute' => 'toFirst',
            'class' => BooleanColumn::class,
        ],
        'lang',
        [
            'attribute' => 'published',
            'class' => BooleanColumn::class,
        ],
        [
            'attribute' => 'createdAt',
            'class' => FormulaColumn::class,
            'value' => function ($model) {
                return \Yii::$app->formatter->asDatetime($model['createdAt']);
            },
        ],
        [
            'attribute' => 'publishedAt',
            'class' => FormulaColumn::class,
            'value' => function ($model) {
                return \Yii::$app->formatter->asDatetime($model['publishedAt']);
            },
        ],
        [
            'attribute' => 'startPublicationDate',
            'class' => FormulaColumn::class,
            'format' => 'raw',
            'value' => function ($model) {
                return \Yii::$app->formatter->asDatetime($model['startPublicationDate']);
            },
        ],
        [
            'attribute' => 'endPublicationDate',
            'class' => FormulaColumn::class,
            'format' => 'raw',
            'value' => function ($model) {
                return \Yii::$app->formatter->asDatetime($model['endPublicationDate']);
            },
        ],
    ],
];

?>
<div class="row button-row">
    <div class="col-md-12 col-sm-12">
        <?= Html::a(
            '<i class="fa fa-plus"> </i> ' . \Yii::t('yiicms', 'Создать страницу'),
            Url::toWithNewReturn(['/admin/pages/add']),
            ['class' => 'btn pull-right btn-primary']
        ) ?>
    </div>
</div>
<div class="col-md-12 col-sm-12">
    <?php Pjax::begin() ?>
    <?php Alert::widget() ?>
    <?= GridView::widget($gridConfig); ?>
    <?php Pjax::end() ?>
</div>
