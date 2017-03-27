<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 12.12.2016
 * Time: 12:26
 */

namespace yiicms\components\core\widgets;

use yii\bootstrap\Widget;
use yiicms\models\core\PmailsIncoming;
use yiicms\models\core\Settings;

class PmailAlert extends Widget
{
    const ALERT_POPUP = 'pmail-incoming-alert';

    public function run()
    {
        if (strpos(\Yii::$app->request->pathInfo, 'pmails') === 0 || null === ($mails = $this->unreadCount())) {
            return '';
        }

        \Yii::$app->cache->set(self::class, [time(), count($mails)], Settings::get('users.pmails.alertBlockTimeout'));

        $this->view->registerJs('$("#' . PmailAlert::ALERT_POPUP . '").modal("show")');
        return $this->render('@theme/views/modules/users/views/pmails/_pmails-incoming-alert', ['mails' => $mails]);
    }

    /**
     * непрочетнные сообщения
     * @return null|PmailsIncoming[]
     */
    private function unreadCount()
    {
        $user = \Yii::$app->user;
        if ($user->isGuest) {
            return null;
        }
        /** @var PmailsIncoming[] $mails */
        $mails = PmailsIncoming::viewUnRead($user->id)->all();

        if (count($mails) === 0) {
            return null;
        }

        if (false === ($cache = \Yii::$app->cache->get(self::class))) {
            return $mails;
        }

        list($lastShowed, $count) = $cache;
        if ($count < count($mails) || $lastShowed < time() - Settings::get('users.pmails.alertBlockTimeout')) {
            return $mails;
        }

        return null;
    }
}
