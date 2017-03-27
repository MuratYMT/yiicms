<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 12.05.2016
 * Time: 16:51
 */
use kartik\grid\ActionColumn;
use kartik\grid\BooleanColumn;
use kartik\grid\ExpandRowColumn;
use kartik\grid\FormulaColumn;
use yii\helpers\Html;
use yiicms\components\core\Url;
use yiicms\models\core\Mails;
use yiicms\modules\admin\components\adminlte\GridView;
use yiicms\modules\admin\models\mails\MailsSearch;

/**
 * @var $this \yii\web\View
 * @var $model MailsSearch
 * @var \yii\data\ActiveDataProvider $dataProvider Data provider
 */

$gridConfig = [
    'id' => 'mails-sended',
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
                /** @var Mails $model */
                return $model->messageText;
            },
        ],
        [
            'attribute' => 'fromLogin',
            'label' => \Yii::t('yiicms', 'Отправитель'),
        ],
        'toLogin',
        'email',
        'subject',
        [
            'attribute' => 'sended',
            'class' => BooleanColumn::class,
            'value' => function ($model) {
                return $model['sentAt'] !== null;
            },
            'label' => \Yii::t('yiicms', 'Отправлено'),
        ],
        [
            'attribute' => 'createdAt',
            'class' => FormulaColumn::class,
            'value' => function ($model) {
                return \Yii::$app->formatter->asDatetime($model['createdAt']);
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
            'template' => '<li>{resend}</li>',
            'dropdown' => true,
            'dropdownOptions' => ['class' => 'pull-right'],
            'dropdownButton' => ['class' => 'btn btn-primary', 'label' => '<i class="fa fa-cogs"></i>'],
            'buttons' => [
                'resend' => function ($url, $model) {
                    return Html::a(
                        '<i class="fa fa-send"></i> ' . \Yii::t('yiicms', 'Отправить повторно'),
                        Url::toWithNewReturn(['/admin/mails/resend', 'mailId' => $model['mailId']])
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
            '<i class="fa fa-pencil"> </i> ' . \Yii::t('yiicms', 'Шаблоны писем'),
            Url::toWithNewReturn(['/admin/mails/templates']),
            ['class' => 'pull-right default']
        ); ?>
    </div>
</div>
<div class="row">
    <div class="col-md-12 col-sm-12">
        <?= GridView::widget($gridConfig); ?>
    </div>
</div>
