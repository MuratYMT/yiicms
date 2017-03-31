<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 06.03.2017
 * Time: 10:04
 */
use yii\helpers\Html;
use yii\web\View;
use yiicms\components\core\widgets\Alert;
use yiicms\models\core\Users;
use yiicms\themes\admin\Asset;

/**
 * @var $this View
 * @var $content string
 */

/** @var Users $user */
$user = Yii::$app->user->identity;

Asset::register($this);

?>

<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= \Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<!-- ADD THE CLASS layout-top-nav TO REMOVE THE SIDEBAR. -->
<body class="hold-transition skin-blue layout-top-nav">
<?php $this->beginBody() ?>
<div class="wrapper">

    <?= $this->render('header.php', ['user' => $user]) ?>
    <!-- Full Width Column -->
    <?= Alert::widget() ?>
    <div class="content-wrapper">
        <div class="container">
            <?= $content ?>
        </div>
    </div>
    <!-- /.content-wrapper -->
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
<!-- ./wrapper -->

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
