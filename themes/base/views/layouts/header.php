<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 06.03.2017
 * Time: 12:01
 */
use yii\helpers\Url;
use yiicms\components\core\blocks\BlocksContainer;
use yii\web\View;
use yiicms\models\core\Settings;
use yiicms\models\core\Users;
use yiicms\themes\admin\widgets\LastIncomingPmails;
use yiicms\themes\admin\widgets\UserAccountMenu;

/**
 * @var $this View
 * @var $user Users
 */

?>
<header class="main-header">
    <nav class="navbar navbar-static-top">
        <div class="container">
            <div class="navbar-header">
                <a href="<?= Url::to(['/']) ?>" class="navbar-brand"><?= Settings::get('core.siteName') ?></a>
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse">
                    <i class="fa fa-bars"></i>
                </button>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse pull-left" id="navbar-collapse">
                <?= BlocksContainer::widget(['position' => 'topNavigation']) ?>
                <form class="navbar-form navbar-left" role="search">
                    <div class="form-group">
                        <input type="text" class="form-control" id="navbar-search-input" placeholder="Search">
                    </div>
                </form>
            </div>
            <!-- /.navbar-collapse -->
            <!-- Navbar Right Menu -->
            <?php if (!Yii::$app->user->isGuest): ?>
                <div class="navbar-custom-menu">
                    <ul class="nav navbar-nav">
                        <!-- Messages: style can be found in dropdown.less-->
                        <?= LastIncomingPmails::widget() ?>
                        <!-- /.messages-menu -->
                        <!-- User Account Menu -->
                        <?= UserAccountMenu::widget(['user' => $user]); ?>
                    </ul>
                </div>
            <?php endif; ?>
            <!-- /.navbar-custom-menu -->
        </div>
        <!-- /.container-fluid -->
    </nav>
</header>
