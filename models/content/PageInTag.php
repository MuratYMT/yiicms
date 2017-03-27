<?php

namespace yiicms\models\content;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "web.contentPagesInTags".
 * @property integer $pageId
 * @property integer $tagId
 * @property Page $page
 * @property Tag $contentTag
 */
class PageInTag extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%contentPagesInTags}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pageId', 'tagId'], 'required'],
            [['pageId', 'tagId'], 'integer'],
            [['pageId'], 'exist', 'skipOnError' => true, 'targetClass' => Page::class, 'targetAttribute' => ['pageId' => 'pageId']],
            [['tagId'], 'exist', 'skipOnError' => true, 'targetClass' => Tag::class, 'targetAttribute' => ['tagId' => 'tagId']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'pageId' => \Yii::t('modules/content', 'Page ID'),
            'tagId' => \Yii::t('modules/content', 'Tag ID'),
        ];
    }

    // ---------------------------------------------------- связи ----------------------------------------------------------------

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
    public function getContentTag()
    {
        return $this->hasOne(Tag::class, ['tagId' => 'tagId']);
    }
}
