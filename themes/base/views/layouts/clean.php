<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 06.03.2017
 * Time: 10:04
 */
use yii\helpers\Html;
use yii\web\View;
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
<?=$content ?>
<?php $this->endBody()?>
</body>
</html>
<?php $this->endPage() ?>
