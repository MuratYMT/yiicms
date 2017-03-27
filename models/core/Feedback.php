<?php

namespace yiicms\models\core;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "web.feedback".
 * @property integer $feedback_id
 * @property integer $creator_id
 * @property string $creator_mail
 * @property string $creator_fio
 * @property string $uid
 * @property integer $section_id
 * @property integer $owner_id
 * @property string $create_time
 * @property string $phone
 * @property string $subject
 * @property integer $last_user
 * @property Users $creator
 * @property Users $owner
 * @property FeedbackMessages[] $feedbackMessages
 */
class Feedback extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'web.feedback';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['creator_id', 'section_id', 'owner_id', 'last_user'], 'integer'],
            [['creator_mail', 'creator_fio', 'uid', 'section_id', 'create_time', 'subject'], 'required'],
            [['create_time'], 'safe'],
            [['creator_mail', 'creator_fio', 'subject'], 'string', 'max' => 255],
            [['uid'], 'string', 'max' => 32],
            [['phone'], 'string', 'max' => 12],
            [['uid'], 'unique'],
            [['creator_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::class, 'targetAttribute' => ['creator_id' => 'userId']],
            [['owner_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::class, 'targetAttribute' => ['owner_id' => 'userId']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'feedback_id' => Yii::t('app', 'Feedback ID'),
            'creator_id' => Yii::t('app', 'Creator ID'),
            'creator_mail' => Yii::t('app', 'Creator Mail'),
            'creator_fio' => Yii::t('app', 'Creator Fio'),
            'uid' => Yii::t('app', 'Uid'),
            'section_id' => Yii::t('app', 'Section ID'),
            'owner_id' => Yii::t('app', 'Owner ID'),
            'create_time' => Yii::t('app', 'Create Time'),
            'phone' => Yii::t('app', 'Phone'),
            'subject' => Yii::t('app', 'Subject'),
            'last_user' => Yii::t('app', 'Last User'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreator()
    {
        return $this->hasOne(Users::class, ['userId' => 'creator_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOwner()
    {
        return $this->hasOne(Users::class, ['userId' => 'owner_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFeedbackMessages()
    {
        return $this->hasMany(FeedbackMessages::class, ['feedback_id' => 'feedback_id']);
    }
}
