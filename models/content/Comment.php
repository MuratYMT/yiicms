<?php

namespace yiicms\models\content;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yiicms\components\core\behavior\TimestampBehavior;
use yiicms\components\core\DateTime;
use yiicms\components\core\TreeHelper;
use yiicms\components\core\TreeTrait;
use yiicms\components\core\validators\CommentValidator;
use yiicms\models\core\Users;

/**
 * This is the model class for table "web.contentComments".
 * @property integer $commentId Идентификатор комментария
 * @property integer $createdAt Время создания
 * @property integer $ownerId Автор комментария
 * @property string $ownerLogin Логин автора комментария
 * @property string $commentText Текст комментария
 * @property integer $estimation Оценка комментария
 * @property integer $lastEditedAt Дата последнего изменения
 * @property integer $lastEditUserId Последний пользователь редактировавший комментарий
 * @property string $lastEditUserLogin Логин последнего пользователя редактировавшего комментарий
 * @property string $ownerIp IP автора комментария
 * @property integer $commentGroup Группа комментариев. Используется чтобы отделять коментарии одной страницы от другой и т . п .
 * --
 * @property Users $ownerUser
 * @property Users $lastEditUser
 * @property UserStat $userStat
 */
class Comment extends ActiveRecord
{
    use TreeTrait;
    const SC_EDIT = 'edit';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%contentComments}}';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAttributes' => ['createdAt'],
                'updatedAttributes' => ['lastEditedAt'],
            ],
        ];
    }

    public function scenarios()
    {
        return [
            self::SCENARIO_DEFAULT => [],
            self::SC_EDIT => ['commentText', '!parentId', '!ownerId'],
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
            [['parentId', 'estimation'], 'default', 'value' => 0],
            [['parentId', 'ownerId', 'estimation', 'lastEditedAt', 'lastEditUserId', 'commentGroup'], 'integer'],
            [['mPath', 'createdAt', 'ownerLogin', 'commentText', 'ownerIp', 'commentGroup'], 'required'],
            [
                ['ownerId'],
                'exist',
                'targetClass' => Users::class,
                'targetAttribute' => ['ownerId' => 'userId'],
                'filter' => ['status' => Users::STATUS_ACTIVE],
                'message' => \Yii::t('modules/content', 'Неверный пользователь'),
            ],
            [
                ['parentId'],
                function ($attribute) {
                    if (!$this->hasErrors()) {
                        if ((int)$this->parentId === 0) {
                            return;
                        }
                        /** @var Comment $parent */
                        $parent = Comment::find()
                            ->where(['commentGroup' => $this->commentGroup, 'commentId' => $this->parentId])
                            ->one(self::getDb());

                        if ($parent === null) {
                            $this->addError($attribute, \Yii::t('modules/content', 'Неизвестный идентифкатор родительского комментария'));
                            return;
                        }
                        if (TreeHelper::detectLoop($parent->mPath, $this->mPath)) {
                            $this->addError(
                                'parentId',
                                \Yii::t('modules/content', 'Невозможно установить родительский комментарий. Обнаружена циклическая ссылка')
                            );
                        }
                    }
                },
            ],
            [['commentText'], 'string', 'max' => 64000],
            [['commentText'], CommentValidator::class],
            [['mPath'], 'string', 'max' => 1000],
            [['ownerLogin', 'lastEditUserLogin'], 'safe'],
            [['ownerIp'], 'ip'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'commentId' => \Yii::t('modules/content', 'Идентификатор комментария'),
            'parentId' => \Yii::t('modules/content', 'Родительский комментарий'),
            'mPath' => \Yii::t('modules/content', 'Материализованный путь'),
            'levelNod' => \Yii::t('modules/content', 'Уровень комментария'),
            'createdAt' => \Yii::t('modules/content', 'Время создания'),
            'ownerId' => \Yii::t('modules/content', 'Автор комментария'),
            'ownerLogin' => \Yii::t('modules/content', 'Логин автора комментария'),
            'commentText' => \Yii::t('modules/content', 'Текст комментария'),
            'estimation' => \Yii::t('modules/content', 'Оценка комментария'),
            'lastEditedAt' => \Yii::t('modules/content', 'Дата последнего изменения'),
            'lastEditUserId' => \Yii::t('modules/content', 'Последний пользователь редактировавший комментарий'),
            'lastEditUserLogin' => \Yii::t('modules/content', 'Логин последнего пользователя редактировавшего комментарий'),
            'ownerIp' => \Yii::t('modules/content', 'IP автора комментария'),
            'commentGroup' => \Yii::t('modules/content', 'Группа комментариев'),
        ];
    }

    public function beforeSave($insert)
    {
        $userId = \Yii::$app->user->id;
        /** @var Users $userObj */
        $userObj = Users::findOne($userId);
        if ($insert) {
            $this->ownerIp = \Yii::$app->request->userIP;
            $this->ownerId = $userId;
            $this->ownerLogin = $userObj->login;
            UserStat::changeComment($userObj);
        } else {
            TreeHelper::updateHierarchicalData($this);
            if ($this->isAttributeChanged('commentText')) {
                $this->lastEditUserId = $userId;
                $this->lastEditUserLogin = $userObj->login;
            }
        }

        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes)
    {
        if ($insert) {
            TreeHelper::setMPath($this);
        }
        parent::afterSave($insert, $changedAttributes);
    }

    public function beforeDelete()
    {
        //перемещаем дочерние категории в родительскую категорию
        $parentId = $this->parentId;
        foreach ($this->children as $comment) {
            $comment->parentId = $parentId;
            if (!$comment->save()) {
                return false;
            }
        }
        unset($this->children);
        return parent::beforeDelete();
    }

    public function afterDelete()
    {
        parent::afterDelete();
        UserStat::changeComment($this->ownerUser, -1);
    }

    /**
     * Определяет время последнего прочтения группы комментариев
     * @param int $commentGroup для какой группы комментариев
     * @return mixed
     */
    public static function readLastReadTime($commentGroup)
    {
        $user = \Yii::$app->user;
        if ($user->isGuest) {
            return \Yii::$app->session->get('last_read_comment_' . $commentGroup);
        } else {
            return Users::findOne($user->id)->getUserData('last_read_comment_' . $commentGroup);
        }
    }

    /**
     * Сохраняет время последнего прочтения группы комментариев
     * @param $commentGroup
     */
    public static function writeLastReadTime($commentGroup)
    {
        $time = DateTime::convertToDbFormat(DateTime::runTime());
        $user = \Yii::$app->user;
        if ($user->isGuest) {
            \Yii::$app->session->set('last_read_comment_' . $commentGroup, $time);
        } else {
            Users::findOne($user->id)->setUserData('last_read_comment_' . $commentGroup, $time);
        }
    }

    /**
     * определяет идентификатор последнего прочтенного комментария
     * @param int $commentGroup группа комментариев
     * @return bool|int false если комментариев нет
     */
    public static function lastReadCommentIdForTree($commentGroup)
    {
        if (null === ($lastReadTime = Comment::readLastReadTime($commentGroup))) {
            return false;
        }

        return Comment::find()
            ->where(['and', ['<', 'createdAt', $lastReadTime], ['commentGroup' => $commentGroup]])
            ->max('commentId');
    }

    /**
     * определяет последний написанный комментарий
     * @param int $commentGroup группа комментариев
     * @return bool|int false если комментариев нет
     */
    public static function lastCommentIdForTree($commentGroup)
    {
        return Comment::find()
            ->where(['commentGroup' => $commentGroup])
            ->max('commentId');
    }

    // ---------------------------------------------------------- связи ------------------------------------------------------------------

    /**
     * @return ActiveQuery
     */
    public function getOwnerUser()
    {
        return $this->hasOne(Users::class, ['userId' => 'ownerId']);
    }

    /**
     * @return ActiveQuery
     */
    public function getLastEditUser()
    {
        return $this->hasOne(Users::class, ['userId' => 'lastEditUserId']);
    }

    public function getUserStat()
    {
        return $this->hasOne(UserStat::class, ['userId' => 'ownerId']);
    }

    // -------------------------------------------------- геттеры и сеттеры -------------------------------------------------------------
}
