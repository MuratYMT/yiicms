<?php

namespace yiicms\models\core;

use yii\db\ActiveRecord;
use yiicms\components\core\behavior\DateTimeBehavior;
use yiicms\components\core\behavior\TimestampBehavior;
use yiicms\components\core\DateTime;
use yiicms\components\core\validators\HtmlFilter;
use yiicms\components\core\validators\WebTextValidator;

/**
 * This is the model class for table "web.mails".
 * @property int $mailId ID письма
 * @property string $toLogin Логин получателя
 * @property string $email Email получателя
 * @property string $subject заголовок письма
 * @property string $messageText текст письма
 * @property DateTime $sentAt дата отправки
 * @property string $backendId идентифкатор процесса cron занимающегося отправкой писем
 * @property DateTime $createdAt дата создания письма
 * @property integer $fromUserId id отправителя
 * @property string $fromLogin
 * @property Users $fromUser
 */
class Mails extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%mails}}';
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            [
                'class' => TimestampBehavior::class,
                'createdAttributes' => ['createdAt']
            ],
            [
                'class' => DateTimeBehavior::class,
                'attributes' => ['sentAt'],
                'format' => DateTimeBehavior::FORMAT_DATETIME
            ],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['toLogin', 'email', 'subject', 'messageText', 'fromUserId'], 'required'],
            [['messageText'], 'string'],
            [['messageText'], WebTextValidator::class],
            [['fromUserId'], 'integer'],
            [['fromUserId'], 'exist', 'targetClass' => Users::class, 'targetAttribute' => ['fromUserId' => 'userId']],
            [['toLogin', 'email'], 'string', 'max' => 255],
            [['email'], 'email'],
            [['subject'], 'string', 'max' => 1000],
            [['subject'], HtmlFilter::class],
            [['backendId'], 'string', 'max' => 32],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'mailId' => \Yii::t('yiicms', 'ID письма'),
            'toLogin' => \Yii::t('yiicms', 'Получатель'),
            'email' => 'E-mail',
            'subject' => \Yii::t('yiicms', 'Тема'),
            'messageText' => \Yii::t('yiicms', 'Текст письма'),
            'sentAt' => \Yii::t('yiicms', 'Время отправки'),
            'backendId' => \Yii::t('yiicms', 'Идентифкатор cron'),
            'createdAt' => \Yii::t('yiicms', 'Дата написания'),
            'fromUserId' => \Yii::t('yiicms', 'Id отправителя'),
        ];
    }

    /**
     * производит отправку сообщения
     * @param bool $resend переотправить письмо если уже было отправлено
     * @return bool|-1 -1 если письмо уже было отправлено и resend = false
     */
    public function sendToReciver($resend = false)
    {
        if (!$resend && $this->sentAt !== null) {
            return -1;
        }

        $result = \Yii::$app->mailer
            ->compose()
            ->setFrom([Settings::get('core.robotMail') => Settings::get('core.siteName')])
            ->setTo([$this->email => $this->toLogin])
            ->setSubject($this->subject)
            ->setHtmlBody($this->messageText)
            ->send();

        if ($result) {
            $this->sentAt = new DateTime();
            $this->save();
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
    public static function send($templateId, $fromUser, $toUser, array $params = [])
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

        $mail = new self;
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

    // -------------------------------------------------- связи ---------------------------------------------------------------

    public function getFromUser()
    {
        return $this->hasOne(Users::class, ['userId' => 'fromUserId']);
    }

    // --------------------------------------------- геттеры и сеттеры ---------------------------------------------------------

    public function getFromLogin()
    {
        return $this->fromUser->login;
    }
}
