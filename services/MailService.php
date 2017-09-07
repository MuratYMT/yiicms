<?php
/**
 * Created by PhpStorm.
 * User: murat
 * Date: 04.09.2017
 * Time: 12:34
 */

namespace yiicms\services;

use yiicms\components\core\DateTime;
use yiicms\models\core\Mails;
use yiicms\models\core\MailsTemplates;
use yiicms\models\core\Settings;
use yiicms\models\core\Users;

class MailService
{
    /**
     * производит отправку сообщения
     * @param Mails $mail
     * @param bool $resend переотправить письмо если уже было отправлено
     * @return bool -1 -1 если письмо уже было отправлено и resend = false
     */
    public function sendToReciver(Mails $mail, $resend = false)
    {
        if (!$resend && $mail->sentAt !== null) {
            return -1;
        }

        $result = \Yii::$app->mailer
            ->compose()
            ->setFrom([Settings::get('core.robotMail') => Settings::get('core.siteName')])
            ->setTo([$mail->email => $mail->toLogin])
            ->setSubject($mail->subject)
            ->setHtmlBody($mail->messageText)
            ->send();

        if ($result) {
            $mail->sentAt = new DateTime();
            $mail->save();
        }

        return $result;
    }

    /**
     * выполяет обработку шаблона для отправки получателю
     * @param string $templateId Id шаблона письма
     * @param Users $fromUser получатель
     * @param Users $toUser отправитель
     * @param array $params
     * @return Mails|false false если отправка не удалась
     */
    public function send($templateId, $fromUser, $toUser, array $params = [])
    {
        $template = MailsTemplates::findTemplate($templateId);
        if ($template === null) {
            return false;
        }

        if (!isset($params['siteName'])) {
            $params['siteName'] = Settings::get('core.siteName');
        }
        if (!isset($params['siteUrl'])) {
            $params['siteUrl'] = \Yii::$app->urlManager->createAbsoluteUrl(\Yii::$app->homeUrl);
        }

        $template->lang = $toUser->lang;
        $template->params = $params;

        $mail = new Mails();
        $mail->fromUserId = $fromUser->userId;
        $mail->email = $toUser->email;
        $mail->toLogin = $toUser->login;

        $mail->subject = $template->renderSubject();
        $mail->messageText = $template->renderTemplate();
        if (!$mail->save()) {
            return false;
        }
        return $mail;
    }
}