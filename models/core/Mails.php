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

    // -------------------------------------------------- связи -------------------------------------------------------

    public function getFromUser()
    {
        return $this->hasOne(Users::class, ['userId' => 'fromUserId']);
    }

    // --------------------------------------------- геттеры и сеттеры ------------------------------------------------

    public function getFromLogin()
    {
        return $this->fromUser->login;
    }
}
