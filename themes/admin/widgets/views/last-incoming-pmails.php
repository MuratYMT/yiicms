<?php
use yii\web\View;
use yiicms\models\core\PmailsIncoming;

/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 28.02.2017
 * Time: 17:01
 */

/**
 * @var $this View
 * @var $pmails PmailsIncoming[]
 * @var $unreaded int
 */

$formatter = Yii::$app->formatter;

?>

<!-- Messages: style can be found in dropdown.less-->
<li class="dropdown messages-menu">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        <i class="fa fa-envelope-o"></i>
        <span class="label label-success"><?= $unreaded ?></span>
    </a>
    <ul class="dropdown-menu">
        <li class="header">
            <?= Yii::t(
                'yiicms',
                'У вас {n, plural, =0{нет непрочитанных сообщений} =1{одно непрочитанное сообщение} one{непрочитанное сообщение} few{непрочитанных сообщения} many{непрочитанных сообщений} other{#}}',
                ['n' => $unreaded]) ?>
        </li>
        <?php if ($unreaded > 0): ?>
            <li>
                <!-- inner menu: contains the actual data -->
                <ul class="menu">
                    <?php foreach ($pmails as $pmail) : ?>
                        <li><!-- start message -->
                            <a href="#">
                                <div class="pull-left">
                                    <img src="<?= $pmail->fromUser->photo->asThumbnail($this, 160, 160) ?>" class="img-circle" alt="User Image"/>
                                </div>
                                <h4>
                                    <?= $pmail->fromUser->login ?>
                                    <small><i class="fa fa-clock-o"></i> <?= $formatter->asRelativeTime($pmail->sentAt); ?></small>
                                </h4>
                                <p><?= substr($pmail->subject, 0, 40) ?></p>
                            </a>
                        </li>
                        <!-- end message -->
                    <?php endforeach; ?>
                </ul>
            </li>
        <?php endif; ?>
        <!--<li class="footer"><a href="#">See All Messages</a></li> -->
    </ul>
</li>
