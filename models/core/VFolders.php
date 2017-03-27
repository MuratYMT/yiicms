<?php

namespace yiicms\models\core;

use yiicms\components\core\Helper;
use yiicms\components\core\TreeHelper;
use yiicms\components\core\TreeTrait;
use yiicms\components\core\validators\TitleFilter;
use yii\db\ActiveRecord;
use yii\db\Query;

/**
 * This is the model class for table "web.vFolders".
 * @property integer $folderId Идентификатор каталога
 * @property string $title Название
 * @property integer $userId Владелец каталога
 * @property Users $user
 * @property VFiles[] $vFiles
 * @property VFolders[] $childFolders
 */
class VFolders extends ActiveRecord
{
    use TreeTrait;

    const SC_RENAME = 'rename';
    const SC_INSERT = 'insert';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%vFolders}}';
    }

    public function scenarios()
    {
        return [
            self::SC_RENAME => ['title'],
            self::SC_INSERT => ['title', '!userId', '!parentId'],
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
            [['parentId', 'title', 'mPath', 'userId'], 'required'],
            [['parentId', 'userId'], 'integer'],
            [['userId'], 'exist', 'targetClass' => Users::class],
            [
                ['parentId'],
                function ($attribute) {
                    if (!$this->hasErrors()) {
                        if ((int)$this->parentId === 0) {
                            $folder = VFolders::find()->where(['userId' => $this->userId])->orderBy(['mPath' => SORT_ASC])->limit(1)->one();
                            if ($folder !== null) {
                                $this->addError($attribute, \Yii::t('yiicms', 'Для пользователя доступна только одна корневая папка'));
                            }
                        } else {
                            $parent = VFolders::findOne(['folderId' => $this->parentId, 'userId' => $this->userId]);
                            if ($parent === null) {
                                $this->addError($attribute, \Yii::t('yiicms', 'Неизвестная родительская папка'));
                                return;
                            }
                            if (TreeHelper::detectLoop($parent->mPath, $this->mPath)) {
                                $this->addError(
                                    'parentId',
                                    \Yii::t('yiicms', 'Невозможно установить родительскую папку. Обнаружена циклическая ссылка')
                                );
                            }
                        }
                    }
                },
            ],
            [['title'], 'string', 'max' => 255],
            [['title'], TitleFilter::class],
            [['mPath'], 'string', 'max' => 1000],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'folderId' => \Yii::t('yiicms', 'Идентификатор Каталога'),
            'parentId' => \Yii::t('yiicms', 'Родительский каталог'),
            'title' => \Yii::t('yiicms', 'Название'),
            'mPath' => \Yii::t('yiicms', 'Материализованный путь'),
            'levelNod' => \Yii::t('yiicms', 'Уровень'),
            'userId' => \Yii::t('yiicms', 'Владелец каталога'),
        ];
    }

    public function beforeSave($insert)
    {
        if (!$insert) {
            TreeHelper::updateHierarchicalData($this);
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
        foreach ($this->childFolders as $childFolder) {
            $childFolder->delete();
        }

        foreach ($this->vFiles as $file) {
            $file->delete();
        }
        return parent::beforeDelete();
    }

    /**
     * выдает иерархический список папок пользователя
     * @param int $userId идентификатор пользователя
     * @return \yiicms\models\core\VFolders[]
     */
    public static function allFolders($userId = null)
    {
        if ($userId === null) {
            $userId = (int)\Yii::$app->user->id;
        }

        $rawData = (new Query())
            ->from(self::tableName())
            ->where(['userId' => $userId])
            ->all();

        $rawData = TreeHelper::build($rawData, 'folderId', 'title', SORT_ASC, false);
        return Helper::populateArray(VFolders::class, $rawData);
    }

    /**
     * корневая папка пользователя
     * @param $userId
     * @return VFolders
     */
    public static function userRootFolder($userId)
    {
        $folder = self::find()->where(['userId' => $userId])->orderBy(['mPath' => SORT_ASC])->limit(1)->one();
        //если папки нет то создает
        if ($folder === null) {
            $folder = new VFolders(['userId' => $userId, 'title' => 'root', 'parentId' => 0, 'scenario' => self::SC_INSERT]);
            $folder->save();
        }

        return $folder;
    }

    // ------------------------------------------ связи --------------------------------------------------------

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::class, ['userId' => 'userId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVFiles()
    {
        return $this->hasMany(VFiles::class, ['folderId' => 'folderId'])->with('loadedFile');
    }

    public function getChildFolders()
    {
        return $this->hasMany(self::class, ['parentId' => 'folderId'])
            ->from(self::tableName() . ' AS parent')
            ->orderBy('title')->with('vFiles');
    }
}
