<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 01.02.2016
 * Time: 15:55
 */

use kartik\grid\ExpandRowColumn;
use yiicms\components\core\Url;
use yii\web\View;
use yiicms\models\core\PmailsIncoming;
use yiicms\modules\users\controllers\PmailsController;
use yiicms\modules\users\models\pmails\PmailsIncomingSearch;
use kartik\grid\ActionColumn;
use kartik\grid\FormulaColumn;
use yiicms\modules\admin\components\adminlte\GridView;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;

/**
 * @var $this View
 * @var $model PmailsIncomingSearch
 * @var $dataProvider ActiveDataProvider
 */

echo GridView::widget([
    'id' => PmailsController::FORM_GRID,
    'dataProvider' => $dataProvider,
    'filterModel' => $model,
    'responsive' => false,
    'hover' => true,
    'columns' => [
        [
            'class' => ExpandRowColumn::class,
            'value' => function () {
                return GridView::ROW_COLLAPSED;
            },
            'detail' => function ($model) {
                /** @var PmailsIncoming $model */
                return $model->msgText;
            },
        ],
        'fromUserLogin',
        [
            'attribute' => 'subject',
            'format' => 'raw',
            'class' => FormulaColumn::class,
            'value' => function ($model) {
                /** @var PmailsIncoming $model */
                if ($model->readed) {
                    return $model->subject;
                } else {
                    return Html::tag('strong', $model->subject);
                }
            },
        ],
        [
            'attribute' => 'sentAt',
            'class' => FormulaColumn::class,
            'format' => 'raw',
            'value' => function ($model) {
                return \Yii::$app->formatter->asDatetime($model['sentAt']);
            },
        ],
        [
            'class' => ActionColumn::class,
            'template' => '<li>{reply}</li><li>{forward}</li><li>{mark-read}</li><li>{mark-unread}</li><li>{del}</li>',
            'dropdownOptions' => ['class' => 'pull-right'],
            'dropdown' => true,
            'dropdownButton' => ['class' => 'btn btn-primary', 'label' => '<i class="fa fa-cogs"></i>'],
            'buttons' => [
                'reply' => function ($url, $model) {
                    return Html::a(
                        '<i class="fa fa-reply"></i> ' . \Yii::t('modules/users', 'Ответить'),
                        Url::toWithNewReturn(['/pmails/reply', 'rowId' => $model['rowId']]),
                        ['title' => \Yii::t('modules/users', 'Ответить на сообщение')]
                    );
                },
                'forward' => function ($url, $model) {
                    return Html::a(
                        '<i class="fa fa-share"></i> ' . \Yii::t('modules/users', 'Переслать'),
                        Url::toWithNewReturn(['/pmails/forward', 'rowId' => $model['rowId']]),
                        ['title' => \Yii::t('modules/users', 'Переслать сообщение другому пользователю'), 'data-pjax' => 0]
                    );
                },
                'mark-read' => function ($url, $model) {
                    return Html::a(
                        '<i class="fa fa-check"></i> ' . \Yii::t('modules/users', 'Отметить прочтенным'),
                        Url::toWithNewReturn(['/pmails/mark-read', 'rowId' => $model['rowId']]),
                        ['title' => \Yii::t('modules/users', 'Отметить сообщение как прочтенное'), 'data-method' => 'post']
                    );
                },
                'mark-unread' => function ($url, $model) {
                    return Html::a(
                        '<i class="fa fa-square-o"></i> ' . \Yii::t('modules/users', 'Отметить не прочтенным'),
                        Url::toWithNewReturn(['/pmails/mark-unread', 'rowId' => $model['rowId']]),
                        ['title' => \Yii::t('modules/users', 'Отметить сообщение как не прочтенное'), 'data-method' => 'post']
                    );
                },
                'del' => function ($url, $model) {
                    return Html::a(
                        '<i class="fa fa-trash"></i> ' . \Yii::t('modules/users', 'Удалить'),
                        Url::toWithNewReturn(['/pmails/del', 'rowId' => $model['rowId']]),
                        [
                            'data-method' => 'post',
                            'data-confirm' => \Yii::t('modules/users', 'Удалить?'),
                            'title' => \Yii::t('modules/users', 'Удалить это сообщение'),
                        ]
                    );
                },

            ],
        ],
    ],
]);
