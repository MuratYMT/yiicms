<?php

namespace yiicms\models\content;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "web.contentPagesInCategories".
 * @property integer $pageId
 * @property integer $categoryId
 * --
 * @property Category $category
 * @property Page $page
 * @property CategoryPermission[] $categoryPermissions
 */
class PageInCategory extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%contentPagesInCategories}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pageId', 'categoryId'], 'required'],
            [['pageId', 'categoryId'], 'integer'],
            [['categoryId'], 'exist', 'skipOnError' => true, 'targetClass' => Category::class, 'targetAttribute' => ['categoryId' => 'categoryId']],
            [['pageId'], 'exist', 'skipOnError' => true, 'targetClass' => Page::class, 'targetAttribute' => ['pageId' => 'pageId']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'pageId' => \Yii::t('modules/content', 'Page ID'),
            'categoryId' => \Yii::t('modules/content', 'Category ID'),
        ];
    }

    // ---------------------------------------------------------- связи -----------------------------------------------------------------

    /**
     * @return ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(Category::class, ['categoryId' => 'categoryId']);
    }

    /**
     * @return ActiveQuery
     */
    public function getPage()
    {
        return $this->hasOne(Page::class, ['pageId' => 'pageId']);
    }

    /**
     * @return ActiveQuery
     */
    public function getCategoryPermissions()
    {
        return $this->hasMany(CategoryPermission::class, ['categoryId' => 'categoryId']);
    }
}
