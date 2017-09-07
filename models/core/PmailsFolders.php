<?php

namespace yiicms\models\core;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "web.PmailsFolders".
 * @property integer $folderId
 * @property integer $userId Владелец папки
 * @property integer $folderType Тип папки исходящая, входящая и т.п.
 * @property string $title Название папки
 * @property Users $user владелец папки
 * @property PmailsIncoming $incomingMails входящие письма
 * @property PmailsOutgoing $outgoingMails исходящие письма
 */
class PmailsFolders extends ActiveRecord
{
    const SC_EDIT = 'edit';

    /** тип папки входящие */
    const TYPE_INCOMING = 0;
    /** тип папки исходящие */
    const TYPE_OUTGOING = 1;
    /** тип папки черновики */
    const TYPE_DRAFT = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%pmailsFolders}}';
    }

    public function scenarios()
    {
        return [
            self::SCENARIO_DEFAULT => [],
            self::SC_EDIT => ['userId', 'folderType', 'title'],
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
            [['folderId', 'userId', 'folderType', 'title'], 'required'],
            [['folderId', 'userId', 'folderType'], 'integer'],
            [['title'], 'string', 'max' => 255],
            [['userId'], 'exist', 'targetClass' => Users::class],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'folderId' => Yii::t('app', 'Folder ID'),
            'userId' => Yii::t('app', 'Владелец папки'),
            'folderType' => Yii::t('app', 'Тип папки исходящая, входящая и т.п.'),
            'title' => Yii::t('app', 'Название папки'),
        ];
    }

    // ---------------------------------------------------- связи -----------------------------------------------------

    public function getUser()
    {
        return $this->hasOne(Users::class, ['userId' => 'userId']);
    }

    public function getIncomingMails()
    {
        return $this->hasMany(PmailsIncoming::class, ['folderId' => 'folderId']);
    }

    public function getOutgoingMails()
    {
        return $this->hasMany(PmailsOutgoing::class, ['folderId' => 'folderId']);
    }
}
