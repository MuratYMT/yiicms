<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 28.02.2017
 * Time: 16:57
 */

namespace yiicms\themes\admin\widgets;

use yii\bootstrap\Widget;
use yiicms\models\core\PmailsIncoming;

class LastIncomingPmails extends Widget
{
    public function run()
    {
        $user = \Yii::$app->user;
        if ($user->isGuest){
            return '';
        }

        $pmails = PmailsIncoming::view(\Yii::$app->user->id)->andWhere(['readed' => 0])
            ->with(['fromUser'])
            ->limit(10)
            ->orderBy(['sentAt' => SORT_DESC])
            ->all();

        $unreaded = (int)PmailsIncoming::view($user->id)->andWhere(['readed' => 0])->count();

        return $this->render('last-incoming-pmails', ['pmails' => $pmails, 'unreaded' => $unreaded]);
    }
}