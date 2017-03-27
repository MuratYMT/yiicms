<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 18.08.2015
 * Time: 10:22
 */

namespace yiicms\modules\admin\models\categories;

use yiicms\components\core\Helper;
use yiicms\components\core\TreeHelper;
use yiicms\models\content\Category;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use yii\db\Query;

class CategoriesSearch extends Model
{
    public $title;
    public $slug;
    public $keywords;
    public $lang;
    public $weight;

    public function rules()
    {
        return [
            [['title', 'slug', 'keywords'], 'string', 'max' => 255],
            [['weight'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'weight' => \Yii::t('yiicms', 'Вес категории'),
            'createdAt' => \Yii::t('yiicms', 'Время создания'),
            'title' => \Yii::t('yiicms', 'Заголовок'),
            'slug' => \Yii::t('yiicms', 'Ярлык'),
            'keywords' => \Yii::t('yiicms', 'Список ключевых слов категории')
        ];
    }

    /**
     * выдать все категории находящиеся в указанном разделе
     * @param array $params
     * @return ArrayDataProvider
     */
    public function search($params)
    {
        $query = (new Query())
            ->select([
                'categoryId',
                'parentId',
                'mPath',
                'weight',
                'createdAt',
                'titleM',
                'description',
                'slug',
                'keywords'
            ])
            ->from(Category::tableName());

        $categories = TreeHelper::build($query->all(Category::getDb()), 'categoryId', 'weight');

        $this->load($params);

        if ($this->validate()) {
            $this->filter($categories);
        }

        $provider = new ArrayDataProvider([
            'allModels' => Helper::populateArray(Category::class, $categories),
            'pagination' => false,
        ]);
        return $provider;
    }

    /**
     * @param array $categories
     */
    protected function filter(&$categories)
    {
        if (!empty($this->title)) {
            foreach ($categories as $key => $category) {
                if (mb_stripos($category['title'], $this->title) === false) {
                    unset($categories[$key]);
                }
            }
        }

        if (!empty($this->slug)) {
            foreach ($categories as $key => $category) {
                if (mb_stripos($category['slug'], $this->slug) === false) {
                    unset($categories[$key]);
                }
            }
        }

        if (!empty($this->keywords)) {
            foreach ($categories as $key => $category) {
                if (mb_stripos($category['keywords'], $this->keywords) === false) {
                    unset($categories[$key]);
                }
            }
        }

        if (!empty($this->lang)) {
            foreach ($categories as $key => $category) {
                if (mb_stripos($category['lang'], $this->lang) === false) {
                    unset($categories[$key]);
                }
            }
        }

        if (!empty($this->weight)) {
            foreach ($categories as $key => $category) {
                if (mb_stripos($category['weight'], $this->weight) === false) {
                    unset($categories[$key]);
                }
            }
        }
    }
}
