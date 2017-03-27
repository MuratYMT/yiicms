<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 10.12.2016
 * Time: 9:23
 */

namespace yiicms\cronjobs;

use yiicms\components\core\cronjob\CronJob;
use yiicms\models\core\Mails;

class MailSender extends CronJob
{
    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->description = 'Отправка электронной почты адресатам';
    }

    public function run()
    {
        $backendId = \Yii::$app->security->generateRandomString();

        if (!$this->grabMails($backendId)) {
            return true;
        }

        foreach (Mails::findAll(['backEndId' => $backendId]) as $mail) {
            $mail->sendToReciver();
        }
        return true;
    }

    /**
     * выполняет блокирование неотправленных писем
     * @param string $backendId
     * @return bool true если есть что отправлять
     */
    private function grabMails($backendId)
    {
        $n = Mails::getDb()->createCommand()
            ->update(Mails::tableName(), ['backEndId' => $backendId], ['backEndId' => null])
            ->execute();

        return $n !== false;
    }
}
