<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 09.03.2017
 * Time: 8:29
 */
use yii\helpers\Html;
use yiicms\models\core\Users;

/**
 * @var $user Users
 */

?>

<li class="dropdown user user-menu">
    <!-- Menu Toggle Button -->
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        <!-- The user image in the navbar-->
        <img src="<?= $user->photo->asThumbnail($this, 160, 160) ?>" class="user-image" alt="User Image">
        <!-- hidden-xs hides the username on small devices so only the image appears. -->
        <span class="hidden-xs"><?= $user->login ?></span>
    </a>
    <ul class="dropdown-menu">
        <!-- The user image in the menu -->
        <li class="user-header">
            <img src="<?= $user->photo->asThumbnail($this, 160, 160) ?>" class="img-circle" alt="User Image">
            <p>
                <?= $user->login ?>
                <small>
                    <?= Yii::t('yiicms', 'Зарегестрирован {date}',
                        ['date' => Yii::$app->formatter->asDate($user->createdAt)]) ?>
                </small>
            </p>
        </li>
        <!-- Menu Body -->
        <li class="user-body">
            <div class="row">
                <div class="col-xs-12 text-center">
                    <?= Html::a(Yii::t('yiicms', 'Личные сообщения'), ['/pmails']) ?>
                </div>
            </div>
            <!-- /.row -->
        </li>
        <!-- Menu Footer-->
        <li class="user-footer">
            <div class="pull-left">
                <?= Html::a(Yii::t('yiicms', 'Профиль'), ['/profile'], ['class' => 'btn btn-default btn-flat']) ?>
            </div>
            <div class="pull-right">
                <?= Html::a(Yii::t('yiicms', 'Выход'), ['/logout'], ['class' => 'btn btn-primary btn-flat']) ?>
            </div>
        </li>
    </ul>
</li>
