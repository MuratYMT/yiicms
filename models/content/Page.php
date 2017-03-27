<?php

namespace yiicms\models\content;

use yii\base\InvalidCallException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\helpers\Inflector;
use yiicms\components\content\CategoryValidator;
use yiicms\components\core\ArrayHelper;
use yiicms\components\core\behavior\DateTimeBehavior;
use yiicms\components\core\behavior\FilesBehavior;
use yiicms\components\core\behavior\JsonArrayBehavior;
use yiicms\components\core\behavior\TimestampBehavior;
use yiicms\components\core\DateTime;
use yiicms\components\core\File;
use yiicms\components\core\Helper;
use yiicms\components\core\RbacHelper;
use yiicms\components\core\TagObj;
use yiicms\components\core\TreeHelper;
use yiicms\components\core\validators\DateTimeValidator;
use yiicms\components\core\validators\HtmlFilter;
use yiicms\components\core\validators\LangValidator;
use yiicms\components\core\validators\WebTextValidator;
use yiicms\models\core\LoadedFiles;
use yiicms\models\core\Users;

/**
 * This is the model class for table "web.contentPages".
 * @property integer $pageId Page ID
 * @property DateTime $createdAt Время создания страницы. При чтении всегда выдает дату во внутреннем формате в UTC
 * При записи можно передать строку которая будет считаться что находится в часовом поясе указанном в \Yii::$app->formatter->timeZone
 * либо объект DateTime
 * @property integer $publishedAt Дата публикации (дата показываемая пользователям). При чтении всегда выдает дату во внутреннем формате в UTC
 * При записи можно передать строку которая будет считаться что находится в часовом поясе указанном в \Yii::$app->formatter->timeZone
 * либо объект DateTime
 * @property integer $ownerId Идентификатор создателя
 * @property string $ownerLogin Логин создателя
 * @property integer $toFirst Выводить ли на первую страницу. 1 - да, 0 - нет
 * @property string $pageType Тип странцы
 * @property integer $opened Разрешать добавлять комментарии. 1 - да, 0 - нет
 * @property string $slug Ярлык страницы
 * @property string $slugFull Ярлык страницы с ведущим id_page-
 * @property string $lang Язык страницы
 * @property string $title Заголовок страницы
 * @property string $announce Анонс
 * @property string $pageText Текст страницы
 * @property integer $commentsGroup Группа коментариев
 * @property DateTime|string $lastEditedAt Время последнего редактирования. При чтении всегда выдает объект DateTime
 * При записи можно передать строку которая будет считаться что находится в часовом поясе указанном в \Yii::$app->formatter->timeZone
 * либо объект DateTime
 * @property integer $lastUserId Последний редактировавший пользователь
 * @property string $lastUserLogin Логин последнего редактировавшего пользователя
 * @property integer $viewCount Количество просмотров страницы
 * @property integer $published 1 - опубликовано, 0 - черновик
 * @property File[] $images Сопроводительные материалы (картинки, видео и т.п.)
 * @property DateTime|string $startPublicationDate время с которого страница доступна. При чтении всегда выдает объект DateTime
 * При записи можно передать строку которая будет считаться что находится в часовом поясе указанном в \Yii::$app->formatter->timeZone
 * либо объект DateTime
 * @property DateTime|string $endPublicationDate время до которого страница доступна. При чтении всегда выдает объект DateTime
 * При записи можно передать строку которая будет считаться что находится в часовом поясе указанном в \Yii::$app->formatter->timeZone
 * либо объект DateTime
 * @property int[] $categoriesIds Категории страницы
 * @property string $keywords значение тега meta name="keywords"
 * @property TagObj[] $tags теги страницы
 * @property string $tagsString теги страницы в виде строки
 * @property bool $isPublished ReadOnly. Првоеряет является ли страница опубликованной в текущий момент времени
 * @property Users $lastUser последний редактировавший пользователь
 * @property PageRevision[] $webPagesRevision все ревизии страницы
 * @property PageInCategory[] $pageInCategories
 * @property Category[] $categories к каким категориям пренадлежит страница
 * @property PageRevision[] $pageHistory
 * @property Users $owner создатель
 * --
 * @property int[] $oldCategoriesIds Категории страницы до редактирования
 * @property Tag[] $tag
 * @property PageInTag[] $pageInTags
 * @property UserStat $userStat
 * @property Comment[] $commentsAsTree комментарии в виде дерева
 * @property int $commentsCount количество комментариев у страницы
 */
class Page extends ActiveRecord
{
    const SC_EDIT = 'edit';

    /** @var  Users */
    private $_owner;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%contentPages}}';
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            [
                'class' => TimestampBehavior::class,
                'createdAttributes' => ['createdAt'],
                'updatedAttributes' => ['lastEditedAt'],
            ],
            [
                'class' => DateTimeBehavior::class,
                'attributes' => ['publishedAt', 'startPublicationDate', 'endPublicationDate'],
                'format' => DateTimeBehavior::FORMAT_DATETIME,
            ],
            [
                'class' => JsonArrayBehavior::class,
                'attributes' => ['categoriesIds'],
            ],
            [
                'class' => FilesBehavior::class,
                'attributes' => ['images'],
            ],
        ]);
    }

    public function scenarios()
    {
        return [
            self::SCENARIO_DEFAULT => [],
            self::SC_EDIT => [
                'toFirst',
                'opened',
                'slug',
                'lang',
                'title',
                'announce',
                'pageText',
                'published',
                'publishedAt',
                'startPublicationDate',
                'endPublicationDate',
                'categoriesIds',
                'keywords',
                'tagsString',
                '!images',
            ],
        ];
    }

    public function isTransactional($operation)
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();
        if (empty($this->categoriesIds)) {
            $this->categoriesIds = [];
        }
        if (empty($this->images)) {
            $this->images = [];
        }
        if (empty($this->tags)) {
            $this->tags = [];
        }
        if ($this->lang === null) {
            $this->lang = \Yii::$app->language;
        }
        if ($this->pageType === null) {
            $this->pageType = 'Default';
        }
        if ($this->viewCount === null) {
            $this->viewCount = 0;
        }
        if ($this->opened === null) {
            $this->opened = 1;
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['publishedAt', 'startPublicationDate', 'endPublicationDate'], 'default'],
            [['ownerLogin', 'pageType', 'lang', 'title', 'categoriesIds'], 'required'],
            [['ownerId', 'toFirst', 'opened', 'lastUserId', 'viewCount', 'published'], 'integer'],
            [
                ['publishedAt', 'startPublicationDate', 'endPublicationDate'],
                DateTimeValidator::class,
                'format' => DateTimeValidator::FORMAT_DATETIME
            ],
            [['pageText'], 'string', 'min' => 10],
            [['announce', 'keywords'], 'string'],
            [['announce', 'pageText', 'keywords'], WebTextValidator::class],
            [
                ['images'],
                function ($attribute) {
                    if ($this->hasErrors()) {
                        return;
                    }
                    $value = $this->images;
                    $query = LoadedFiles::find()->where(['id' => array_keys($value)]);
                    if ((int)$query->count() !== count($value)) {
                        $this->addError($attribute, \Yii::t('modules/content', 'Неизвестные изображения'));
                    }
                },
            ],
            [['lang'], 'string', 'max' => 10],
            [['lang'], LangValidator::class],
            [['title'], 'string', 'max' => 255],
            [['slug'], 'string', 'max' => 235],
            [
                ['slug', 'lang'],
                'unique',
                'targetAttribute' => ['slug', 'lang'],
                'message' => \Yii::t('modules/content', 'Ярлык уже используется'),
            ],
            [['categoriesIds'], 'each', 'rule' => ['integer']],
            [['categoriesIds'], CategoryValidator::class],
            [['title', 'keywords'], HtmlFilter::class],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'pageId' => \Yii::t('modules/content', 'Page ID'),
            'createdAt' => \Yii::t('modules/content', 'Дата создания страницы'),
            'publishedAt' => \Yii::t('modules/content', 'Дата публикации'),
            'ownerId' => \Yii::t('modules/content', 'Идентификатор создателя'),
            'ownerLogin' => \Yii::t('modules/content', 'Автор'),
            'toFirst' => \Yii::t('modules/content', 'На первую'),
            'pageType' => \Yii::t('modules/content', 'Тип странцы'),
            'opened' => \Yii::t('modules/content', 'Разрешать добавлять комментарии'),
            'slug' => \Yii::t('modules/content', 'Ярлык'),
            'lang' => \Yii::t('modules/content', 'Язык'),
            'title' => \Yii::t('modules/content', 'Заголовок страницы'),
            'announce' => \Yii::t('modules/content', 'Анонс'),
            'pageText' => \Yii::t('modules/content', 'Текст страницы'),
            'commentsGroup' => \Yii::t('modules/content', 'Группа коментариев'),
            'lastEditedAt' => \Yii::t('modules/content', 'Время последнего редактирования'),
            'lastUserId' => \Yii::t('modules/content', 'Последний редактировавший пользователь'),
            'lastUserLogin' => \Yii::t('modules/content', 'Логин последнего редактировавшего пользователя'),
            'viewCount' => \Yii::t('modules/content', 'Количество просмотров страницы'),
            'published' => \Yii::t('modules/content', 'Опубликовать'),
            'images' => \Yii::t('modules/content', 'Сопроводительные материалы (картинки, видео и т.п.)'),
            'startPublicationDate' => \Yii::t('modules/content', 'Дата начала публикации'),
            'endPublicationDate' => \Yii::t('modules/content', 'Дата окончания публикации'),
            'categoriesIds' => \Yii::t('modules/content', 'Категории страницы'),
            'keywords' => \Yii::t('modules/content', 'Ключевые слова'),
            'tags' => \Yii::t('modules/content', 'Теги'),
            'tagsString' => \Yii::t('modules/content', 'Теги'),
        ];
    }

    /**
     * @inheritDoc
     */
    public function __set($name, $value)
    {
        if ($name === 'slug') {
            $value = $this->pageId . '-' . $value;
        } elseif ($name === 'tags') {
            $value = TagObj::saveToJson($value);
        }
        parent::__set($name, $value);
    }

    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        $value = parent::__get($name);
        if ($name === 'slug') {
            $value = ltrim($value, '1234567890-');
        } elseif ($name === 'tags') {
            $value = TagObj::createFromJson($value);
        }
        return $value;
    }

    public function beforeSave($insert)
    {
        $userId = \Yii::$app->user->id;
        $this->_owner = Users::findOne($userId);

        if ($insert) {
            //добавление
            $this->ownerId = $userId;
            $this->ownerLogin = $this->_owner->login;
            if ($this->commentsGroup === null) {
                $this->commentsGroup = \Yii::$app->security->generateRandomString(32);
            }
        } else {
            if ($this->isAttributeChanged('announce') || $this->isAttributeChanged('pageText')) {
                //отметка редактирования
                $this->lastUserId = $userId;
                $this->lastUserLogin = $this->_owner->login;
            }
        }

        if ($this->publishedAt === null) {
            $this->publishedAt = DateTime::runTime();
        }

        if (empty($this->slug)) {
            $this->slug = Inflector::slug($this->title);
        }

        $this->processImages();

        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        //создаем ревизию
        if ($insert || (array_key_exists('announce', $changedAttributes) || array_key_exists('pageText', $changedAttributes))) {
            $this->createRevision($this->_owner);
        }

        if (array_key_exists('categoriesIds', $changedAttributes)) {
            $oldCategoriesIds = $changedAttributes['categoriesIds'];
            $this->processCategories(empty($oldCategoriesIds) ? [] : json_decode($oldCategoriesIds, true));
        }

        if (array_key_exists('tags', $changedAttributes)) {
            $oldTags = $changedAttributes['tags'];
            $this->processTags(empty($oldTags) ? [] : TagObj::createFromJson($oldTags));
        }

        if ($insert) {
            //добавляем страницу в статистику пользователя
            UserStat::changePage($this->_owner);
        }
    }

    public function afterDelete()
    {
        parent::afterDelete();
        foreach ($this->images as $image) {
            $image->loadedFile->delete();
        }
        UserStat::changePage($this->owner, -1);
    }

    /**
     * проверяет есть ли у пользователя указанное разрешение
     * разрешение есть в том случае если это разрешение есть у пользователя хотябы в одной из категорий в
     * которую входит страница
     * @param string $permission проверяемое разрешение
     * @param int|int[] $categoriesIds если не null, то проверяет разрешение только в указанном узле
     * @return bool
     */
    public function can($permission, $categoriesIds = null)
    {
        $user = \Yii::$app->user;
        $userId = $user->isGuest ? 0 : (int)$user->id;

        $categories = $categoriesIds === null ? $this->categories : Category::findAll($categoriesIds);

        foreach ($categories as $category) {
            if ($category->can($permission)) {
                return true;//есть разрешение на действие с любой страницей
            }

            /** @noinspection NotOptimalIfConditionsInspection */
            if (in_array($permission, CategoryPermission::$permissionsPageOwn, true) &&
                $userId === (int)$this->ownerId &&
                $category->can($permission . 'Own')
            ) {
                return true;//есть разрешение на действие со своей страницей
            }
        }
        return false;
    }

    /**
     * добавляет просмотр странице
     * @param int $count количество добавляемых просмотров
     */
    public function addView($count = 1)
    {
        $this->updateCounters(['viewCount' => $count]);
    }

    /**
     * отвязывает картинку от страницы. физически картинка не удаляется до тех пор пока страница не сохранится
     * @param string $id id удаляемого изображения
     * @return $this
     * @throws \Exception
     */
    public function unlinkImage($id)
    {
        $images = $this->images;
        if (!isset($images[$id])) {
            return $this;
        }
        unset($images[$id]);
        $this->images = $images;
        return $this;
    }

    /**
     * пересчитывает узлы в которых состоит страница
     */
    public function recountCategories()
    {
        $this->categoriesIds = PageInCategory::find()
            ->select('categoryId')
            ->where(['pageId' => $this->pageId])
            ->column();
    }

    /**
     * пересчитывает теги в которых состоит страница
     */
    public function recountTags()
    {
        /** @var PageInTag[] $pits */
        $pits = PageInTag::find()->with('contentTag')->where(['pageId' => $this->pageId])->all();

        $result = [];
        foreach ($pits as $pit) {
            $contentTag = $pit->contentTag;
            $result[$contentTag->tagId] = $contentTag->tag;
        }
        $this->tags = $result;
    }

    /**
     * добавляет условие что все страницы должны быть опубликованными
     * @param ActiveQuery $query
     * @return ActiveQuery
     */
    public static function wherePublished($query)
    {
        $currentDate = DateTime::convertToDbFormat(DateTime::runTime());

        $tbl = static::tableName();

        $query->andWhere([
            'and',
            "$tbl.[[published]] = 1",
            ['or', "$tbl.[[startPublicationDate]] is null", ['<=', "$tbl.[[startPublicationDate]]", $currentDate]],
            ['or', "$tbl.[[endPublicationDate]] is null", ['>=', "$tbl.[[endPublicationDate]]", $currentDate]],
        ]);

        return $query;
    }

    /**
     * выборка страницы по ярлыку и языку
     * @param string $slugFull ярлык страницы
     * @return null|Page
     */
    public static function findBySlug($slugFull)
    {
        $ar = explode('-', $slugFull, 2);
        if (count($ar) === 2 && is_numeric($ar[0])) {
            $pageId = $ar[0];
        } else {
            return null;
        }
        return static::findOne(['pageId' => $pageId]);
    }

    /**
     * создает запрос к базе данных для выдачи страниц которые доступны в указанном узле для указанного разрешения
     * @param string $permission для какого разрешения
     * @param Category $root в какой категории искать
     * @param bool $recursive включать страницы из вложенных категорий
     * @return ActiveQuery
     */
    public static function query($permission, Category $root = null, $recursive = true)
    {
        $query = static::find()->distinct()
            ->joinWith('pageInCategories.categoryPermissions')
            ->where([
                CategoryPermission::tableName() . '.[[permission]]' => $permission,
                CategoryPermission::tableName() . '.[[roleName]]' => ArrayHelper::getColumn(RbacHelper::rolesRecursiveForUser(), 'name'),
            ]);

        if ($root === null) {
            return $query;
        }

        if ($recursive) {
            $query->joinWith('pageInCategories.category')
                ->andWhere([
                    'or',
                    ['like', Category::tableName() . '.[[mPath]]', $root->mPath . '^%', false],
                    [PageInCategory::tableName() . '.[[categoryId]]' => $root->categoryId],
                ]);
        } else {
            $query->andWhere([PageInCategory::tableName() . '.[[categoryId]]' => $root->categoryId]);
        }
        return $query;
    }

    /**
     * Возвращает массив страниц в указанной категории доступные пользователю для просомтра
     * @param Category $category
     * @param bool $recursive показывать страницы из вложенных категорий
     * @param string $lang на каком языке требуются страницы
     * @return false|ActiveQuery false если доступ к просмотру страниц в этой категории отсутствует
     */
    public static function visiblePagesForUser($category, $recursive = true, $lang = null)
    {
        if ($lang === null) {
            $lang = \Yii::$app->language;
        }
        $permission = CategoryPermission::CATEGORY_VIEW;

        if ($category->can($permission)) {
            return Page::wherePublished(Page::query($permission, $category, $recursive))
                ->andWhere(['lang' => $lang]);
        }
        return false;
    }

    /**
     * @param Users $user модель пользователя выполняющего редактирвоание
     * @return bool удалось или нет создать ревизию
     */
    private function createRevision($user)
    {
        //создаем ревизию
        $revision = new PageRevision([
            'userId' => $user->userId,
            'userLogin' => $user->login,
            'pageId' => $this->pageId,
            'title' => $this->title,
            'pageText' => $this->pageText,
            'announce' => $this->announce,
            'userIp' => \Yii::$app->request->userIP,
        ]);
        return $revision->save();
    }

    /**
     * привязывает страницу к узлам
     * @param int[] $oldCategories старый массив категорий
     * @throws \yii\db\Exception
     */
    private function processCategories($oldCategories)
    {
        $pageId = $this->pageId;
        $newCategories = $this->categoriesIds;

        /**
         * @var int[] $added
         * @var int[] $removed
         */
        list($added, $removed) = ArrayHelper::diffValues($newCategories, $oldCategories);

        foreach ($removed as $categoryId) {
            if (null !== ($pin = PageInCategory::findOne(['pageId' => $pageId, 'categoryId' => $categoryId]))) {
                $pin->delete();
            }
        }

        foreach ($added as $categoryId) {
            $pin = new PageInCategory(['pageId' => $pageId, 'categoryId' => $categoryId]);
            $pin->save();
        }
    }

    /**
     * привязывает теги к странице
     * @param TagObj[] $old старый массив узлов
     * @throws \yii\db\Exception
     */
    private function processTags($old)
    {
        $pageId = $this->pageId;

        /**
         * @var int[] $added
         * @var int[] $removed
         */
        list($added, $removed) = ArrayHelper::diffValues(array_keys($this->tags), array_keys($old));

        foreach ($removed as $tagId) {
            if (null !== ($pit = PageInTag::findOne(['pageId' => $pageId, 'tagId' => $tagId]))) {
                $pit->delete();
            }
        }

        foreach ($added as $tagId) {
            $pit = new PageInTag(['pageId' => $pageId, 'tagId' => $tagId]);
            $pit->save();
        }
    }

    /**
     * выполняет прикрепление файла изображения к странице
     */
    private function processImages()
    {
        $oldImages = File::createFromJson($this->getOldAttribute('images'));
        $newImages = $this->images;

        /**
         * @var int[] $added
         * @var int[] $removed
         */
        list($added, $removed) = ArrayHelper::diffValues(array_keys($newImages), array_keys($oldImages));

        foreach ($removed as $id) {
            $oldImages[$id]->loadedFile->delete();
        }

        foreach ($added as $id) {
            $loadedFile = $newImages[$id]->loadedFile;
            $loadedFile->persistent = 1;
            $loadedFile->save();
        }
    }

    // -------------------------------------------------------- связи ---------------------------------------------------------

    /**
     * @return ActiveQuery
     */
    public function getLastUser()
    {
        return $this->hasOne(Users::class, ['userId' => 'lastUserId']);
    }

    /**
     * @return ActiveQuery
     */
    public function getPageHistory()
    {
        return $this->hasMany(PageRevision::class, ['pageId' => 'pageId']);
    }

    public function getPageInCategories()
    {
        return $this->hasMany(PageInCategory::class, ['pageId' => 'pageId']);
    }

    /**
     * @return ActiveQuery
     */
    public function getCategories()
    {
        return $this->hasMany(Category::class, ['categoryId' => 'categoryId'])->via('pageInCategories');
    }

    public function getTags()
    {
        return $this->hasMany(Tag::class, ['tagId' => 'tagId'])->via('pageInTags');
    }

    public function getPageInTags()
    {
        return $this->hasMany(PageInTag::class, ['pageId' => 'pageId']);
    }

    /**
     * @return ActiveQuery|Users
     */
    public function getOwner()
    {
        if ($this->_owner !== null) {
            return $this->_owner;
        }
        return $this->hasOne(Users::class, ['userId' => 'ownerId']);
    }

    // ------------------------------------------------- геттеры и сеттеры -----------------------------------------------

    private $_commentsCount;

    public function getCommentsCount()
    {
        if ($this->_commentsCount === null) {
            $this->_commentsCount = (new Query())
                ->from(Comment::tableName())
                ->where(['commentGroup' => $this->commentsGroup])
                ->count();
        }
        return $this->_commentsCount;
    }

    /**
     * @param Users $owner
     */
    public function setOwner($owner)
    {
        // сбрасываем связь
        $this->owner = null;
        $this->_owner = $owner;
        $this->ownerId = $owner->userId;
        $this->ownerLogin = $owner->login;
    }

    public function getIsPublished()
    {
        if (!$this->published) {
            return false;
        }

        $current = DateTime::runTime();

        //старт публикации еще не наступил
        $start = $this->startPublicationDate;
        if (!empty($start) && $start > $current) {
            return false;
        }

        //завершение публикации уже наступило
        $end = $this->endPublicationDate;
        return !(!empty($end) && $end < $current);
    }

    /**
     * @param string $tagsString
     */
    public function setTagsString($tagsString)
    {
        $this->tags = Tag::stringToTags($tagsString);
    }

    /**
     * @return string
     */
    public function getTagsString()
    {
        $tags = [];
        foreach ($this->tags as $tag) {
            $tags[] = $tag->title;
        }
        return implode('; ', $tags);
    }

    public function getSlugFull()
    {
        if ($this->pageId === null) {
            throw new InvalidCallException('Save page model before use SlugFull');
        }
        return $this->pageId . '-' . $this->slug;
    }

    public function getOldCategoriesIds()
    {
        $oldCategories = $this->getOldAttribute('categoriesIds');
        return empty($oldCategories) ? [] : json_decode($oldCategories, true);
    }

    /**
     * @return Comment[]
     */
    public function getCommentsAsTree()
    {
        $callBack = function ($query) {
            /** @var $query ActiveQuery */
            $query->asArray = false;
        };
        $query = Comment::find()
            ->joinWith(['userStat' => $callBack, 'ownerUser' => $callBack])
            ->where(['commentGroup' => $this->commentsGroup]);

        //строим дерево
        $rows = TreeHelper::build($query->asArray()->all(), 'commentId', ['createdAt' => [SORT_ASC, false], 'commentId' => [SORT_ASC, true]]);
        return Helper::populateArray(Comment::class, $rows, 'commentId');
    }
}
