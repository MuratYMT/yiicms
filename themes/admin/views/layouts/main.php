<?php
use yii\helpers\Html;
use yii\web\View;
use yiicms\models\core\Users;
use yiicms\modules\admin\components\adminlte\Menu;
use yiicms\themes\admin\Asset;

/* @var $this View */
/* @var $content string */

$adminLte = Asset::register($this);
$directoryAsset = $adminLte->baseUrl;

/** @var Users $user */
$user = \Yii::$app->user->identity;

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= \Yii::$app->language ?>">
<head>
    <meta charset="<?= \Yii::$app->charset ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body class="hold-transition <?= $adminLte->skin ?> sidebar-mini">
<?php $this->beginBody() ?>
<div class="wrapper">

    <?= $this->render('header.php', ['user' => $user]) ?>

    <aside class="main-sidebar">

        <section class="sidebar">

            <!-- Sidebar user panel -->
            <div class="user-panel">
                <div class="pull-left image">
                    <img src="<?= $user->photo->asThumbnail($this, 160, 160) ?>" class="img-circle" alt="User Image"/>
                </div>
                <div class="pull-left info">
                    <p><?= $user->login ?></p>

                    <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
                </div>
            </div>

            <!-- search form -->
            <form action="#" method="get" class="sidebar-form">
                <div class="input-group">
                    <input type="text" name="q" class="form-control" placeholder="Search..."/>
                    <span class="input-group-btn">
                <button type='submit' name='search' id='search-btn' class="btn btn-flat"><i class="fa fa-search"></i>
                </button>
              </span>
                </div>
            </form>
            <!-- /.search form -->

            <?= Menu::widget(['options' => ['class' => 'sidebar-menu']]) ?>

        </section>

    </aside>

    <?= $this->render('content.php', ['content' => $content]) ?>

    <footer class="main-footer">
        <div class="container">
            <div class="pull-right hidden-xs">
                <b>Version</b> 2.3.8
            </div>
            <strong>Copyright &copy; 2017 Murat Yeskendirov.</strong> All rights reserved.
        </div>
        <!-- /.container -->
    </footer>

</div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
