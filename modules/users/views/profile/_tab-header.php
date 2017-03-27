<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 07.03.2017
 * Time: 10:27
 */
use yii\helpers\Url;

$currentUrl = \Yii::$app->request->pathInfo;

?>

<ul class="nav nav-tabs">
    <li class="<?= $currentUrl === 'profile' ? 'active' : '' ?>"><a href="<?= Url::to(['/profile']) ?>"><?= Yii::t('yiicms', 'Профиль') ?></a></li>
    <li class="<?= $currentUrl === 'profile/photo' ? 'active' : '' ?>"><a href="<?= Url::to('/profile/photo') ?>"><?= Yii::t('yiicms', 'Фотография') ?></a></li>
    <li class="<?= $currentUrl === 'profile/change-password' ? 'active' : '' ?>"><a href="<?= Url::to('/profile/change-password') ?>"><?= Yii::t('yiicms', 'Смена пароля') ?></a></li>
</ul>
