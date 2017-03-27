<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 01.02.2016
 * Time: 15:52
 */

use yii\widgets\Pjax;
use yiicms\assets\CommonAsset;
use yiicms\components\core\Url;
use yii\web\View;
use yiicms\modules\users\controllers\PmailsController;
use yiicms\modules\users\models\pmails\PmailsOutgoingSearch;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;

/**
 * @var View $this
 * @var string $activeTab
 * @var $model PmailsOutgoingSearch
 * @var $dataProvider ActiveDataProvider
 */

CommonAsset::register($this);

?>

<div class="row button-row">
    <div class="col-md-12 col-sm-12">
        <?= Html::a(
            '<i class="fa fa-plus"> </i> ' . \Yii::t('modules/users', 'Новое сообщение'),
            Url::toWithNewReturn(['pmails/add']),
            ['class' => 'btn pull-right btn-primary']
        ) ?>
    </div>
</div>

<div class="row">
    <div class="col-md-12 col-sm-12">
        <div class="nav-tabs-custom">
            <?php Pjax::begin() ?>
            <ul class="nav nav-tabs">
                <li role="presentation" <?= $activeTab === PmailsController::ST_INCOMING ? 'class="active"' : '' ?>>
                    <?= Html::a(
                        \Yii::t('modules/users', 'Входящие'),
                        Url::to(['/pmails', 'activeTab' => PmailsController::ST_INCOMING]),
                        ['aria-controls' => 'nodes', 'role' => 'tab']
                    ) ?>
                </li>
                <li role="presentation" <?= $activeTab === PmailsController::ST_SENDED ? 'class="active"' : '' ?>>
                    <?= Html::a(
                        \Yii::t('modules/users', 'Отправленные'),
                        Url::to(['/pmails', 'activeTab' => PmailsController::ST_SENDED]),
                        ['aria-controls' => 'nodes', 'role' => 'tab']
                    ) ?>
                </li>
                <li role="presentation" <?= $activeTab === PmailsController::ST_DRAFT ? 'class="active"' : '' ?>>
                    <?= Html::a(
                        \Yii::t('modules/users', 'Черновики'),
                        Url::to(['/pmails', 'activeTab' => PmailsController::ST_DRAFT]),
                        ['aria-controls' => 'nodes', 'role' => 'tab']
                    ) ?>
                </li>
            </ul>
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="<?= $activeTab ?>">
                    <?php switch ($activeTab) {
                        case PmailsController::ST_INCOMING:
                            echo $this->render('_incoming-grid', ['dataProvider' => $dataProvider, 'model' => $model]);
                            break;
                        case PmailsController::ST_SENDED:
                            echo $this->render('_sended-grid', ['dataProvider' => $dataProvider, 'model' => $model]);
                            break;
                        case PmailsController::ST_DRAFT:
                            echo $this->render('_draft-grid', ['dataProvider' => $dataProvider, 'model' => $model]);
                            break;
                    } ?>
                </div>
            </div>
            <?php Pjax::end() ?>
        </div>
    </div>
</div>