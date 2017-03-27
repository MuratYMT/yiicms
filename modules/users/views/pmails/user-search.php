<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 02.02.2016
 * Time: 9:56
 */

use kartik\grid\FormulaColumn;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\Pjax;
use yiicms\components\core\Url;
use yiicms\components\core\widgets\Alert;
use yiicms\modules\admin\components\adminlte\GridView;
use yiicms\modules\admin\models\users\UsersSearch;

/**
 * @var $this View
 * @var $model UsersSearch
 * @var $dataProvider ActiveDataProvider
 */

$gridConfig = [
    'id' => 'users-grid',
    'dataProvider' => $dataProvider,
    'filterModel' => $model,
    'responsive' => true,
    'hover' => true,
    'columns' => [
        [
            'attribute' => 'login',
            'format' => 'raw',
            'class' => FormulaColumn::class,
            'value' => function ($model) {
                return Html::a($model['login'], Url::toCurrent(['receiverId' => $model['userId']]));
            },
        ],
        [
            'attribute' => 'fio',
            'label' => \Yii::t('modules/users', 'Ф.И.О.'),
        ],
    ],
];

?>

<div class="row">
    <div class="col-md-12 col-sm-12">
        <?php Pjax::begin()?>
        <?php Alert::widget()?>
        <?= GridView::widget($gridConfig); ?>
        <?php Pjax::end()?>
    </div>
</div>
