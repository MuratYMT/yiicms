<?php

namespace yiicms\models\content;

use yiicms\models\core\Users;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "web.contentUserStat".
 * @property integer $userId
 * @property integer $pagesCount
 * @property integer $commentsCount
 */
class UserStat extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%contentUserStat}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pagesCount', 'commentsCount'], 'default', 'value' => 0],
            [['userId'], 'required'],
            [['userId', 'pagesCount', 'commentsCount'], 'integer'],
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
            'pagesCount' => Yii::t('yiicms', 'Pages Count'),
            'commentsCount' => Yii::t('yiicms', 'Comments Count'),
        ];
    }

    /**
     * добавляет количество комментариев пользователю
     * @param Users $user
     * @param int $commentsCount сколько комментариев добавить
     * @return bool
     */
    public static function changeComment(Users $user, $commentsCount = 1)
    {
        $model = self::findOne(['userId' => $user->userId]);
        if ($model === null) {
            $model = new self(['userId' => $user->userId, 'commentsCount' => $commentsCount]);
            return $model->save();
        } else {
            return $model->updateCounters(['commentsCount' => $commentsCount]);
        }
    }

    /**
     * добавляет количество страниц пользователю
     * @param Users $user
     * @param int $pagesCount сколько страниц добавить
     * @return bool
     */
    public static function changePage(Users $user, $pagesCount = 1)
    {
        $model = self::findOne(['userId' => $user->userId]);
        if ($model === null) {
            $model = new self(['userId' => $user->userId, 'pagesCount' => $pagesCount]);
            return $model->save();
        } else {
            return $model->updateCounters(['pagesCount' => $pagesCount]);
        }
    }
}
