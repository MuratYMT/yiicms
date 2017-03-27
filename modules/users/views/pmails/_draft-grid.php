<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 01.02.2016
 * Time: 15:56
 */

use kartik\grid\ExpandRowColumn;
use yiicms\components\core\Url;
use yii\web\View;
use yiicms\models\core\PmailsOutgoing;
use yiicms\modules\users\controllers\PmailsController;
use yiicms\modules\users\models\pmails\PmailsOutgoingSearch;
use kartik\grid\ActionColumn;
use kartik\grid\FormulaColumn;
use yiicms\modules\admin\components\adminlte\GridView;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;

/**
 * @var $this View
 * @var $model PmailsOutgoingSearch
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
                /** @var PmailsOutgoing $model */
                return $model->msgText;
            },
        ],
        [
            'attribute' => 'toUsersList',
            'format' => 'raw',
            'class' => FormulaColumn::class,
            'value' => function ($model) {
                /** @var PmailsOutgoing $model */
                $logins = $model->toUsersList;
                $trunc = false;
                if (count($logins) > 10) {
                    $logins = array_slice($logins, 0, 10);
                    $trunc = true;
                }
                return implode('; ', $logins) . ($trunc ? ' и другие' : '');
            },
        ],
        'subject',
        [
            'class' => ActionColumn::class,
            'template' => '<li>{edit}</li><li>{send}</li><li>{mark-read}</li><li>{mark-unread}</li><li>{del}</li>',
            'dropdownOptions' => ['class' => 'pull-right'],
            'dropdown' => true,
            'dropdownButton' => ['class' => 'btn btn-primary', 'label' => '<i class="fa fa-cogs"></i>'],
            'buttons' => [
                'edit' => function ($url, $model) {
                    return Html::a(
                        '<i class="fa fa-pencil"></i> ' . \Yii::t('modules/users', 'Редактировать'),
                        Url::toWithNewReturn(['/pmails/edit', 'rowId' => $model['rowId']]),
                        ['title' => \Yii::t('modules/users', 'Редактировать это сообщение')]
                    );
                },
                'send' => function ($url, $model) {
                    return Html::a(
                        '<i class="fa fa-share"></i> ' . \Yii::t('modules/users', 'Отправить'),
                        Url::toWithNewReturn(['/pmails/send', 'rowId' => $model['rowId']]),
                        [
                            'title' => \Yii::t('modules/users', 'Отправить это сообщение'),
                            'data-confirm' => \Yii::t('modules/users', 'Отправить это сообщение?'),
                            'data-method' => 'post',
                        ]
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
