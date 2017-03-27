<?php

namespace yiicms\models\content;

use yiicms\components\core\behavior\TimestampBehavior;
use yiicms\components\core\validators\WebTextValidator;
use yiicms\models\core\Users;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "web.contentPagesRevision".
 * @property integer $rowId Идентификатор строки
 * @property integer $userId Идентифкатор пользователя создавшего эту версию страницы
 * @property string $userLogin Логин пользователя создавшего эту версию страницы
 * @property integer $pageId Идентифкатор страницы
 * @property string $title Заголовок
 * @property string $pageText Текст страницы
 * @property string|\DateTime $createdAt Время создания этой версии страницы. При чтении всегда выдает объект DateTime
 * При записи можно передать строку которая будет считаться что находится в часовом поясе указанном в \Yii::$app->formatter->timeZone
 * либо объект DateTime
 * @property string $announce Анонс
 * @property string $userIp IP адрес с которого было произведено создание этой версии страницы
 * @property Page $page
 * @property Users $user
 */
class PageRevision extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%contentPagesRevision}}';
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            [
                'class' => TimestampBehavior::class,
                'createdAttributes' => ['createdAt'],
            ],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['userId', 'userLogin', 'pageId', 'title'], 'required'],
            [['userId', 'pageId'], 'integer'],
            [['pageText', 'announce'], 'string'],
            [['pageText', 'announce'], WebTextValidator::class],
            [['userLogin', 'title'], 'string', 'max' => 255],
            [['userIp'], 'ip']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'rowId' => \Yii::t('modules/content', 'Идентификатор строки'),
            'userId' => \Yii::t('modules/content', 'Идентифкатор пользователя создавшего эту версию страницы'),
            'userLogin' => \Yii::t('modules/content', 'Логин пользователя создавшего эту версию страницы'),
            'pageId' => \Yii::t('modules/content', 'Идентифкатор страницы'),
            'title' => \Yii::t('modules/content', 'Заголовок'),
            'pageText' => \Yii::t('modules/content', 'Текст страницы'),
            'createdAt' => \Yii::t('modules/content', 'Время создания этой версии страницы'),
            'announce' => \Yii::t('modules/content', 'Анонс'),
            'userIp' => \Yii::t('modules/content', 'IP адрес с которого было произведено создание этой версии страницы'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPage()
    {
        return $this->hasOne(Page::class, ['pageId' => 'pageId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::class, ['userId' => 'userId']);
    }
}
