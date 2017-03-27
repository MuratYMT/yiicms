<?php
use yii\helpers\Html;
use yiicms\models\core\Users;
use yiicms\themes\admin\widgets\LastIncomingPmails;
use yiicms\themes\admin\widgets\UserAccountMenu;

/* @var $this \yii\web\View */
/* @var $user Users */
?>

<header class="main-header">

    <?= Html::a('<span class="logo-mini">APP</span><span class="logo-lg">' . Yii::$app->name . '</span>', Yii::$app->homeUrl, ['class' => 'logo']) ?>

    <nav class="navbar navbar-static-top" role="navigation">

        <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Toggle navigation</span>
        </a>

        <div class="navbar-custom-menu">

            <ul class="nav navbar-nav">

                <!-- Messages: style can be found in dropdown.less-->
                <?= LastIncomingPmails::widget() ?>

                <!-- User Account: style can be found in dropdown.less -->

                <?= UserAccountMenu::widget(['user'=>$user]); ?>
            </ul>
        </div>
    </nav>
</header>
