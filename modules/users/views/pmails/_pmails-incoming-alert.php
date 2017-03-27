<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 12.12.2016
 * Time: 12:38
 */
use yiicms\components\core\Url;
use yiicms\components\core\widgets\PmailAlert;
use yii\web\View;
use yiicms\models\core\PmailsIncoming;
use yii\bootstrap\Html;
use yii\web\JsExpression;

/**
 * @var $this View
 * @var $mails PmailsIncoming[]
 */

?>
<div id="<?= PmailAlert::ALERT_POPUP ?>" class="modal fade">
    <div id="" class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                <h3 class="modal-title"><?= Yii::t('yiicms', 'У вас есть непрочитанные личные сообщения'); ?></h3>
            </div>
            <div class="modal-body">
                <ul>
                    <?php foreach ($mails as $mail) : ?>
                        <li><?= $mail->subject ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="modal-footer">
                <?= Html::a(
                    '<i class="fa fa-close"> </i> ' . \Yii::t('modules/realty', 'Перейти к чтению'),
                    Url::to(['/pmails', 'activeTab' => 'incoming']),
                    ['class' => 'btn pull-right btn-default']
                ); ?>
                <?= Html::a(
                    '<i class="fa fa-close"> </i> ' . \Yii::t('modules/realty', 'Закрыть'),
                    null,
                    [
                        'class' => 'btn pull-left btn-warning',
                        'onclick' => new JsExpression('$(\'#' . PmailAlert::ALERT_POPUP . '\').modal(\'hide\');')
                    ]
                ); ?>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
</div>