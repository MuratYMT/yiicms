<?php

namespace yiicms\models\core;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "web.pmailsUserStat".
 * @property integer $userId
 * @property integer $notReadCount количетсво не прочитанных сообщений
 * @property integer $totalCount общее количество сообщений
 * @property integer $subscribe уведомлять на почту о личных сообщениях
 * --
 * @property Users $user
 */
class PmailsUserStat extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%pmailsUserStat}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['notReadCount', 'totalCount', 'subscribe'], 'default', 'value' => 0],
            [['userId', 'totalCount'], 'required'],
            [['userId', 'notReadCount', 'totalCount', 'subscribe'], 'integer'],
            [['userId'], 'exist', 'targetClass' => Users::class],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'userId' => Yii::t('yiicms', 'User ID'),
            'notReadCount' => Yii::t('yiicms', 'Not Read Count'),
            'totalCount' => Yii::t('yiicms', 'Total Count'),
            'subscribe' => Yii::t('yiicms', 'Subscribe'),
        ];
    }

    /**
     * добавляет пользователю количество непрочитанных сообщений
     * @param Users $user какому пользователю
     * @param int $count сколько добавить
     * @return bool
     */
    public static function changePmNotReadCount(Users $user, $count)
    {
        $model = self::findOne(['userId' => $user->userId]);
        if ($model === null) {
            $model = new self(['userId' => $user->userId, 'notReadCount' => $count]);
            return $model->save();
        } else {
            return $model->updateCounters(['notReadCount' => $count]);
        }
    }

    /**
     * добавляет пользователю общее количество сообщений
     * @param Users $user какому пользователю
     * @param int $count сколько добавить
     * @return bool
     */
    public static function changePmTotalCount(Users $user, $count)
    {
        $model = self::findOne(['userId' => $user->userId]);
        if ($model === null) {
            $model = new self(['userId' => $user->userId, 'totalCount' => $count]);
            return $model->save();
        } else {
            return $model->updateCounters(['totalCount' => $count]);
        }
    }

    //--------------------------------------------------------- связи -----------------------------------------------------------

    public function getUser()
    {
        return $this->hasOne(Users::class, ['userId' => 'userId']);
    }
}
