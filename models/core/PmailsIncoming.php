<?php

namespace yiicms\models\core;

use yiicms\components\core\behavior\DateTimeBehavior;
use yiicms\components\core\DateTime;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "web.pmailsIncoming".
 * @property integer $rowId
 * @property integer $talkId
 * @property integer $toUserId
 * @property integer $fromUserId
 * @property string $fromUserLogin
 * @property string $subject
 * @property string $msgText
 * @property integer $readed 0 не прочтено 1 прочтено
 * @property integer $folderId
 * @property DateTime $sentAt
 * @property PmailsFolders $folder
 * @property Users $toUser
 * @property Users $fromUser
 */
class PmailsIncoming extends ActiveRecord
{
    const SC_INSERT = 'insert';
    const SC_MARK_READ = 'read';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%pmailsIncoming}}';
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            [
                'class' => DateTimeBehavior::class,
                'attributes' => ['sentAt'],
                'format' => DateTimeBehavior::FORMAT_DATETIME,
            ],
        ]);
    }

    public function scenarios()
    {
        return [
            self::SCENARIO_DEFAULT => [],
            self::SC_INSERT => [],
            self::SC_MARK_READ => [],
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
            [['rowId', 'talkId', 'toUserId', 'fromUserId', 'fromUserLogin', 'subject', 'msgText', 'folderId'], 'required'],
            [['rowId', 'talkId', 'toUserId', 'fromUserId', 'readed', 'folderId'], 'integer'],
            [['msgText'], 'string'],
            [['msgText'], 'string', 'min' => 10, 'max' => 65000],
            [['fromUserLogin', 'subject'], 'string', 'max' => 255],
            [['toUserId'], 'exist', 'targetClass' => Users::class, 'targetAttribute' => ['toUserId' => 'userId']],
            [['folderId'], 'exist', 'targetClass' => PmailsFolders::class, 'filter' => ['userId' => $this->toUserId]],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'rowId' => Yii::t('yiicms', 'Row ID'),
            'talkId' => Yii::t('yiicms', 'Talk ID'),
            'toUserId' => Yii::t('yiicms', 'Получатель'),
            'fromUserId' => Yii::t('yiicms', 'Отправитель'),
            'fromUserLogin' => Yii::t('yiicms', 'Отправитель'),
            'subject' => Yii::t('yiicms', 'Тема'),
            'msgText' => Yii::t('yiicms', 'Текст сообщения'),
            'readed' => Yii::t('yiicms', 'Прочтено'),
            'folderId' => Yii::t('yiicms', 'Папка'),
            'sentAt' => Yii::t('yiicms', 'Отправлено'),
        ];
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($insert) {
            PmailsUserStat::changePmNotReadCount($this->toUser, 1);
            PmailsUserStat::changePmTotalCount($this->toUser, 1);
        } else {
            if (array_key_exists('readed', $changedAttributes)) {
                if ((int)$this->readed === 1) {
                    PmailsUserStat::changePmNotReadCount($this->toUser, -1);
                } else {
                    PmailsUserStat::changePmNotReadCount($this->toUser, 1);
                }
            }
        }
    }

    public function afterDelete()
    {
        parent::afterDelete();
        if (!$this->readed) {
            PmailsUserStat::changePmNotReadCount($this->toUser, -1);
        }
        PmailsUserStat::changePmTotalCount($this->toUser, -1);
    }

    /**
     * отмечает сообщение прочитанным
     * @return bool
     */
    public function markRead()
    {
        $this->readed = 1;
        $scenario = $this->scenario;
        $this->scenario = self::SC_MARK_READ;
        $result = $this->save();
        $this->scenario = $scenario;
        return $result;
    }

    /**
     * отмечает сообщение не прочитанным
     * @return bool
     */
    public function markUnRead()
    {
        $this->readed = 0;
        $scenario = $this->scenario;
        $this->scenario = self::SC_MARK_READ;
        $result = $this->save();
        $this->scenario = $scenario;
        return $result;
    }

    /**
     * письма написанные пользователю
     * @param int $userId
     * @return ActiveQuery
     */
    public static function view($userId)
    {
        return self::find()->where(['toUserId' => $userId]);
    }

    /**
     * непрочтенные письма написанные пользователю
     * @param int $userId
     * @return ActiveQuery
     */
    public static function viewUnRead($userId)
    {
        return self::view($userId)->andWhere(['readed' => 0]);
    }

    // ------------------------------------------------------- связи -----------------------------------------------------------

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
    public function getToUser()
    {
        return $this->hasOne(Users::class, ['userId' => 'toUserId']);
    }

    public function getFromUser()
    {
        return $this->hasOne(Users::class, ['userId' => 'fromUserId']);
    }
}
