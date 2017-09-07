<?php

namespace yiicms\models\core;

use yiicms\components\core\ArrayHelper;
use yiicms\components\core\behavior\DateTimeBehavior;
use yiicms\components\core\behavior\JsonArrayBehavior;
use yiicms\components\core\DateTime;
use yiicms\components\core\validators\HtmlFilter;
use yiicms\components\core\validators\WebTextValidator;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yiicms\components\YiiCms;

/**
 * This is the model class for table "web.pmailsOutgoing".
 * @property integer $talkId
 * @property integer $fromUserId
 * @property array $toUsersList
 * @property string $trgmToUsers
 * @property string $subject
 * @property string $msgText
 * @property integer $sended
 * @property DateTime $sentAt
 * @property integer $folderId
 * @property integer $rowId
 * @property PmailsFolders $folder
 * @property Users $fromUser
 * @property Users|Users[] $toUsers при чтении из свойста всегда выдается в виде Users[].
 * При записи в свойство можно указать как массив получателей так и единичного получателя
 * @property string $fromUserLogin логин отправителя
 * @property string $toUsersLogins логины получателей в строку через ;
 */
class PmailsOutgoing extends ActiveRecord
{
    const SC_EDIT = 'pmEdit';
    const SC_SEND = 'send';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%pmailsOutgoing}}';
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            [
                'class' => DateTimeBehavior::class,
                'attributes' => ['sentAt'],
                'format' => DateTimeBehavior::FORMAT_DATETIME,
            ],
            [
                'class' => JsonArrayBehavior::class,
                'attributes' => ['toUsersList'],
            ],
        ]);
    }

    public function scenarios()
    {
        return [
            self::SCENARIO_DEFAULT => [],
            self::SC_EDIT => ['folderId', 'subject', 'msgText', 'toUsersList'],
            self::SC_SEND => [],
        ];
    }

    public function isTransactional($operation)
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['folderId'], 'default'],
            [['talkId', 'fromUserId', 'subject', 'msgText', 'rowId'], 'required'],
            [['talkId', 'fromUserId', 'sended', 'folderId', 'rowId'], 'integer'],
            [['sentAt'], 'safe'],
            [['subject'], 'string', 'max' => 255],
            [['subject'], HtmlFilter::class],
            [['msgText'], 'string', 'min' => 10, 'max' => 65000],
            [['msgText'], WebTextValidator::class],
            [['fromUserId'], 'exist', 'skipOnError' => true, 'targetClass' => Users::class, 'targetAttribute' => ['fromUserId' => 'userId']],
            [['folderId'], 'exist', 'targetClass' => PmailsFolders::class, 'filter' => ['userId' => $this->fromUserId]],
            [
                ['toUsersList'],
                function ($attribute) {
                    if (!$this->hasErrors()) {
                        $userIds = array_keys($this->toUsersList);
                        $users = Users::findAll(['userId' => $userIds]);
                        if (count($userIds) !== count($users)) {
                            $this->addError($attribute, Yii::t('yiicms', 'Неизвестный получатель'));
                        }
                    }
                },
            ],
            [['trgmToUsers',], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'fromUserId' => Yii::t('yiicms', 'Отправитель'),
            'fromUserLogin' => Yii::t('yiicms', 'Отправитель'),
            'toUsersList' => Yii::t('yiicms', 'Получатели'),
            'toUsersLogins' => Yii::t('yiicms', 'Получатели'),
            'subject' => Yii::t('yiicms', 'Тема'),
            'msgText' => Yii::t('yiicms', 'Текст сообщения'),
            'sended' => Yii::t('yiicms', 'Флаг отправки'),
            'sentAt' => Yii::t('yiicms', 'Время отправки'),
            'folderId' => Yii::t('yiicms', 'Папка'),
        ];
    }

    public function sendMessage()
    {
        $this->sended = 1;
        $scenario = $this->scenario;
        $this->scenario = self::SC_SEND;
        $result = YiiCms::$app->pmailService->outgoingPmailSave($this);
        $this->scenario = $scenario;
        return $result;
    }

    /**
     * не отправленные письма пользователя
     * @param int $userId
     * @return ActiveQuery
     */
    public static function viewDraft($userId)
    {
        return self::view($userId)->andWhere(['sended' => 0]);
    }

    /**
     * отправленные письма пользователя
     * @param int $userId
     * @return ActiveQuery
     */
    public static function viewSended($userId)
    {
        return self::view($userId)->andWhere(['sended' => 1]);
    }

    /**
     * письма написанные пользователем
     * @param int $userId
     * @return ActiveQuery
     */
    public static function view($userId)
    {
        return self::find()->where(['fromUserId' => $userId]);
    }

    /**
     * используется для отправки личных сообщений скриптами
     * @param Users $fromUser
     * @param Users[]|Users $toUsers
     * @param string $subject
     * @param string $message
     * @return false|PmailsOutgoing false если не отправлено
     */
    public static function sendTo($fromUser, $toUsers, $subject, $message)
    {
        $pmailOutgoing = new self([
            'fromUserId' => $fromUser->userId,
            'toUsers' => $toUsers,
            'subject' => $subject,
            'msgText' => $message,
            'sended' => 1,
        ]);

        $pmailOutgoing->scenario = self::SC_EDIT;

        if (!YiiCms::$app->pmailService->outgoingPmailSave($pmailOutgoing)) {
            return false;
        }

        return $pmailOutgoing;
    }

    // ----------------------------------------------------- связи ----------------------------------------------------

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFolder()
    {
        return $this->hasOne(PmailsFolders::class, ['folderId' => 'folderId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFromUser()
    {
        return $this->hasOne(Users::class, ['userId' => 'fromUserId']);
    }

    // ----------------------------------------------- гететры и сеттеры ------------------------------------------------------

    /** @var Users[] */
    private $_toUsers;

    /**
     * @param Users|Users[] $users
     */
    public function setToUsers($users)
    {
        /** @var Users[] $users */
        $users = ArrayHelper::asArray($users);
        $usersList = [];
        foreach ($users as $user) {
            $usersList[$user->userId] = $user->login;
        }

        $this->toUsersList = $usersList;
        $this->_toUsers = $users;
    }

    public function getToUsers()
    {
        if ($this->_toUsers === null) {
            $usersIds = array_keys($this->toUsersList);
            $this->_toUsers = Users::findAll($usersIds);
        }
        return $this->_toUsers;
    }

    public function getFromUserLogin()
    {
        if ($this->fromUser !== null) {
            return $this->fromUser->login;
        }
        return null;
    }

    public function getToUsersLogins()
    {
        if (!empty($this->toUsersList)) {
            return implode(', ', $this->toUsersList);
        }
        return null;
    }
}
