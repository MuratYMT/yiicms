<?php

namespace yiicms\models\content;

use yiicms\components\core\TagObj;
use yiicms\components\core\validators\HtmlFilter;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\Inflector;

/**
 * This is the model class for table "web.contentTags".
 * @property integer $tagId Идентификатор тега
 * @property string $title Имя тега
 * @property integer $pageCount Сколько страниц в этом теге
 * @property string $slug ярлык
 * @property TagObj $tag транспортный объект
 * @property Page[] $pages страницы которые входят в тег
 * @property PageInTag $pagesInTags
 */
class Tag extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%contentTags}}';
    }

    public function scenarios()
    {
        return [
            self::SCENARIO_DEFAULT => ['title'],
        ];
    }

    public function isTransactional($operation)
    {
        return true;
    }

    public function init()
    {
        parent::init();
        if ($this->pageCount === null) {
            $this->pageCount = 0;
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'slug', 'pageCount'], 'required'],
            [['pageCount'], 'integer'],
            [['title'], 'string', 'max' => 80],
            [['title'], HtmlFilter::class],
            [['slug'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'tagId' => \Yii::t('modules/content', 'Идентификатор тега'),
            'title' => \Yii::t('modules/content', 'Имя тега'),
            'pageCount' => \Yii::t('modules/content', 'Сколько страниц в этом теге'),
            'slug' => \Yii::t('modules/content', 'Ярлык'),
        ];
    }

    public function beforeSave($insert)
    {
        if (!$insert || empty($this->slug)) {
            $this->slug = Inflector::slug($this->title);
        }
        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes)
    {
        if (!$insert && (isset($changedAttributes['title']) || isset($changedAttributes['slug']))) {
            foreach ($this->pages as $page) {
                $page->recountTags();
                $page->save();
            }
        }
        parent::afterSave($insert, $changedAttributes);
    }

    /** @var array переменная для временного хранени значения страниц в которых использовался тег перед удалением */
    private $_inPages = [];

    public function beforeDelete()
    {
        //запоминаем в каких страницах используется этот тэг
        $this->_inPages = $this->pages;
        return parent::beforeDelete();
    }

    public function afterDelete()
    {
        foreach ($this->_inPages as $page) {
            $page->recountTags();
            $page->save();
        }
        parent::afterDelete();
    }

    /**
     * выдает Query для доступа к страницам доступным для пользователя для просмотра
     * @return ActiveQuery
     */
    public function visiblePagesForUser()
    {
        return Page::wherePublished(
            Page::query(CategoryPermission::CATEGORY_VIEW)
                ->joinWith('pageInTags')
                ->andWhere([PageInTag::tableName() . '.[[tagId]]' => $this->tagId])
        );
    }

    /**
     * находит тег по его ярлыку
     * @param string $slug ярлык
     * @return static
     */
    public static function findBySlug($slug)
    {
        return self::findOne(['slug' => $slug]);
    }

    /**
     * функция подготавливает строковое представление тегов введенное пользователем в массив
     * заодно исключает дублирование тегов и заносит новые теги в базу данных
     * @param string $tagString
     * @return TagObj[]
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public static function stringToTags($tagString)
    {
        /**
         * 1 ищем по хешу тега совпадения в базе данных
         * 2 все что не найдено добавляем в базу данных
         */
        $tagsTitles = explode(';', str_replace(',', ';', $tagString));
        /** @noinspection NotOptimalIfConditionsInspection */
        if (count($tagsTitles) > 0 && !empty($tagsTitles[0])) {
            $trans = self::getDb()->beginTransaction();
            try {
                $slugs = [];
                foreach ($tagsTitles as $title) {
                    $title = trim($title);
                    $slugs[Inflector::slug($title)] = $title;
                }

                //определяем теги которые уже в базе данных
                /** @var Tag[] $tags */
                $tags = Tag::find()->where(['slug' => array_keys($slugs)])->all();

                $result = [];
                foreach ($tags as $tag) {
                    if (isset($slugs[$tag->slug])) {
                        $result[$tag->tagId] = $tag->tag;
                        unset($slugs[$tag->slug]);
                    }
                }
                //вносим новые теги в базу
                foreach ($slugs as $slug => $title) {
                    $tag = new Tag();
                    $tag->title = $title;
                    $tag->slug = $slug;
                    $tag->save();

                    $result[$tag->tagId] = $tag->tag;
                }

                $trans->commit();
                return $result;
            } catch (\Exception $e) {
                $trans->rollBack();
                throw $e;
            }
        } else {
            return [];
        }
    }

    // ------------------------------------------------------------- связи --------------------------------------------------------------------

    public function getPagesInTag()
    {
        return $this->hasMany(PageInTag::class, ['tagId' => 'tagId']);
    }

    public function getPages()
    {
        return $this->hasMany(Page::class, ['pageId' => 'pageId'])->via('pagesInTag');
    }

    // ------------------------------------------------------- геттеры и сетттеры -------------------------------------------------------------

    public function getTag()
    {
        return new TagObj(['tagId' => $this->tagId, 'title' => $this->title, 'slug' => $this->slug]);
    }
}
