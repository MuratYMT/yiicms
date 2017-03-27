<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 11.03.2015
 * Time: 8:35
 */
use kartik\grid\ActionColumn;
use kartik\grid\FormulaColumn;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yiicms\components\core\Url;
use yiicms\components\core\widgets\Alert;
use yiicms\models\core\Users;
use yiicms\modules\admin\components\adminlte\GridView;
use yiicms\modules\admin\models\users\UsersSearch;

/**
 * @var $this \yii\web\View
 * @var $model Users
 * @var $dataProvider \yii\data\ActiveDataProvider
 */

$gridConfig = [
    'id' => 'users-grid',
    'dataProvider' => $dataProvider,
    'filterModel' => $model,
    'responsive' => false,
    'hover' => true,
    'columns' => [
        [
            'attribute' => 'login',
        ],
        [
            'attribute' => 'fio',
        ],
        [
            'attribute' => 'email',
        ],
        [
            'attribute' => 'createdAt',
            'class' => FormulaColumn::class,
            'value' => function ($model) {
                return \Yii::$app->formatter->asDatetime($model['createdAt']);
            },
        ],
        [
            'attribute' => 'visitedAt',
            'class' => FormulaColumn::class,
            'value' => function ($model) {
                return \Yii::$app->formatter->asDatetime($model['visitedAt']);
            },
        ],
        [
            'attribute' => 'status',
            'class' => FormulaColumn::class,
            'value' => function ($model) {
                return Users::statusLabel($model['status']);
            },
        ],
        [
            'class' => FormulaColumn::class,
            'value' => function ($model) {
                return UsersSearch::rolesForUser($model['userId']);
            },
            'label' => \Yii::t('yiicms', 'Назаначеннные роли'),
        ],
        [
            'class' => ActionColumn::class,
            'template' => '<li>{password}</li><li>{permission}</li><li>{roles}</li>',
            'dropdownOptions' => ['class' => 'pull-right'],
            'dropdown' => true,
            'dropdownButton' => ['class' => 'btn btn-primary', 'label' => '<i class="fa fa-cogs"></i>'],
            'buttons' => [
                'password' => function ($url, $model) {
                    return Html::a(
                        '<i class="fa fa-exchange"></i> ' . \Yii::t('yiicms', 'Сменить пароль'),
                        Url::toWithNewReturn(['/admin/users/change-password', 'userId' => $model['userId']]),
                        ['data-pjax' => 0, 'title' => \Yii::t('yiicms', 'Сменить пароль')]
                    );
                },
                'permission' => function ($url, $model) {
                    return Html::a(
                        '<i class="fa fa-file-o"></i> ' . \Yii::t('yiicms', 'Разрешения'),
                        Url::toWithNewReturn(['/admin/users/permission', 'userId' => $model['userId']]),
                        ['data-pjax' => 0, 'title' => \Yii::t('yiicms', 'Просмотреть назначенные пользователю разрешения')]
                    );
                },
                'roles' => function ($url, $model) {
                    return Html::a(
                        '<i class="fa fa-users fa-2"> </i> ' . \Yii::t('yiicms', 'Роли'),
                        Url::toWithNewReturn(['/admin/users/roles', 'userId' => $model['userId']]),
                        ['data-pjax' => 0, 'title' => \Yii::t('yiicms', 'Управление ролями пользователя')]
                    );
                },
            ],
        ],
    ],
];
?>
<div class="row">
    <div class="col-md-12 col-sm-12">
        <?php Pjax::begin() ?>
        <?php Alert::widget() ?>
        <?= GridView::widget($gridConfig); ?>
        <?php Pjax::end() ?>
    </div>
</div>
