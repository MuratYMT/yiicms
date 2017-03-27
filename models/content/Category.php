<?php

namespace yiicms\models\content;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\Inflector;
use yiicms\components\core\ArrayHelper;
use yiicms\components\core\behavior\MultiLangBehavior2;
use yiicms\components\core\behavior\TimestampBehavior;
use yiicms\components\core\DateTime;
use yiicms\components\core\Helper;
use yiicms\components\core\RbacHelper;
use yiicms\components\core\TreeHelper;
use yiicms\components\core\TreeTrait;
use yiicms\components\core\validators\HtmlFilter;

/**
 * This is the model class for table "web.contentCategories".
 * @property integer $categoryId Идентифкатор категории
 * @property integer $weight Вес категории для сортировки
 * @property string|DateTime $createdAt Время создания. При чтении всегда выдает дату во внутреннем формате в UTC
 * При записи можно передать строку которая будет считаться что находится в часовом поясе указанном в \Yii::$app->formatter->timeZone
 * либо объект DateTime
 * @property string $title Заголовок
 * @property array $titleM массив заголовков на разных языках
 * @property string $description Описание
 * @property string $slug Ярлык
 * @property string $keywords Список ключевых слов категории
 * @property CategoryPermission[] $permissions назначенные разрешения
 * @property Page[] $pagesBranch страницы включая дочерние категории
 * @property Page[] $pages страницы только этой категории
 * @method attributeRulesLang() @see MultiLangBehavior2::attributeRulesLang()
 * @method attributeLabelsLang() @see MultiLangBehavior2::attributeLabelsLang()
 * @method string renderMultilang($activeForm, $attribute) @see MultiLangBehavior2::renderMultilang()
 */
class Category extends ActiveRecord
{
    use TreeTrait;

    const SC_EDIT = 'edit';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%contentCategories}}';
    }

    public function behaviors()
    {
        return [
            [
                'class' => MultiLangBehavior2::class,
                'attributes' => [
                    'title' => [
                        [
                            ['string', 'max' => 235],
                            [HtmlFilter::class]
                        ],
                        \Yii::t('yiicms', 'Заголовок')
                    ],
                ],
                'trgmIndex' => false,
            ],
            [
                'class' => TimestampBehavior::class,
                'createdAttributes' => ['createdAt'],
            ],
        ];
    }

    public function scenarios()
    {
        return [
            self::SCENARIO_DEFAULT => [],
            self::SC_EDIT => array_merge(array_keys($this->attributeLabelsLang()),
                ['parentId', 'description', 'slug', 'weight', 'keywords', '!mPath'])
        ];
    }

    public function isTransactional($operation)
    {
        return true;
    }

    public function init()
    {
        parent::init();
        if (empty($this->weight)) {
            $this->weight = 0;
        }
    }

    /**
     * @inheritdoc
     * @throws \yii\base\InvalidParamException
     */
    public function rules()
    {
        return [
            [['parentId', 'weight'], 'default', 'value' => 0],
            [['mPath'], 'default', 'value' => ''],
            [['parentId', 'weight'], 'integer'],
            [['title'], 'required'],
            [
                ['parentId'],
                function ($attribute) {
                    if (!$this->hasErrors()) {
                        if ((int)$this->parentId === 0) {
                            return;
                        }
                        $parent = $this->parent;
                        if ($parent === null) {
                            $this->addError($attribute, \Yii::t('modules/content', 'Неизвестная родительская категория'));
                            return;
                        }
                        if (!$this->isNewRecord && TreeHelper::detectLoop($parent->mPath, $this->mPath)) {
                            $this->addError(
                                'parentId',
                                \Yii::t('modules/content', 'Невозможно установить родительскую категорию. Обнаружена циклическая ссылка')
                            );
                        }
                    }
                },
            ],
            [['description', 'keywords'], 'string', 'max' => 32000],
            [['mPath'], 'string', 'max' => 1000],
            [['title', 'slug'], 'string', 'max' => 235],
            [['title', 'description', 'keywords'], HtmlFilter::class],
            [['slug'], 'unique', 'message' => \Yii::t('modules/content', 'Такой ярлык уже существует')],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge($this->attributeLabelsLang(), [
            'categoryId' => \Yii::t('modules/content', 'Идентифкатор категории'),
            'parentId' => \Yii::t('modules/content', 'Идентификатор родительской категории'),
            'mPath' => \Yii::t('modules/content', 'Материализованный путь в дереве'),
            'levelNod' => \Yii::t('modules/content', 'Уровень категории в дереве'),
            'weight' => \Yii::t('modules/content', 'Вес категории'),
            'createdAt' => \Yii::t('modules/content', 'Время создания'),
            'title' => \Yii::t('modules/content', 'Заголовок'),
            'description' => \Yii::t('modules/content', 'Описание'),
            'slug' => \Yii::t('modules/content', 'Ярлык'),
            'keywords' => \Yii::t('modules/content', 'Список ключевых слов категории'),
        ]);
    }

    public function beforeSave($insert)
    {
        if (empty($this->slug)) {
            $this->slug = Inflector::slug($this->title);
        }

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
        //перемещаем дочерние категории в родительскую категорию
        $parentId = $this->parentId;
        foreach ($this->children as $child) {
            $child->parentId = $parentId;
            if (false === $child->save()) {
                return false;
            }
        }
        unset($this->children);
        return parent::beforeDelete();
    }

    public function afterDelete()
    {
        foreach ($this->pages as $page) {
            $page->recountCategories();
            $page->save();
        }
        parent::afterDelete();
    }

    /**
     * проверяет есть ли у текущего пользователя указанное разрешение
     * @param string $permission разрешение
     * @return bool
     */
    public function can($permission)
    {
        //определяем роли назначенные пользователю
        $roles = ArrayHelper::getColumn(RbacHelper::rolesRecursiveForUser(), 'name');

        $count = CategoryPermission::find()
            ->where(['categoryId' => $this->categoryId, 'roleName' => $roles, 'permission' => $permission])
            ->count();
        return $count > 0;
    }

    /**
     * Устанавливает права для роли
     * @param string $roleName Для какой роли
     * @param array $permissions Массив разрешений для установки. В качестве ключей используются идентификаторы разрешений,
     * а в качестве значений - значение разрешения true|false. Если в передаваемом массиве нет какого то разрешения то оно отнимается
     * @param bool $recursive
     * @return bool
     */
    public function setPermission($roleName, $permissions, $recursive = false)
    {
        $forAdd = [];
        $forRemove = [];
        foreach (CategoryPermission::$permissions as $permission) {
            if (isset($permissions[$permission]) && $permissions[$permission]) {
                $forAdd[] = $permission;
            } else {
                $forRemove[] = $permission;
            }
        }
        return $this->revoke($roleName, $forRemove, $recursive) && $this->assign($roleName, $forAdd, $recursive);
    }

    /**
     * Назначает указанные разрешения для роли
     * @param string $roleName для какой роли
     * @param string[]|string $permissions какие разрешения надо назначить
     * @param bool $recursive
     * @return bool
     */
    public function assign($roleName, $permissions, $recursive = false)
    {
        $permissions = (array)$permissions;
        $trans = self::getDb()->beginTransaction();
        try {
            $existPerm = CategoryPermission::find()
                ->select(['permission'])
                ->where(['roleName' => $roleName, 'categoryId' => $this->categoryId, 'permission' => $permissions])
                ->column();

            $notExsistPerm = array_diff($permissions, $existPerm);

            foreach ($notExsistPerm as $permission) {
                $cp = new CategoryPermission(['roleName' => $roleName, 'categoryId' => $this->categoryId, 'permission' => $permission]);
                if (!$cp->save()) {
                    $trans->rollBack();
                    return false;
                }
            }

            if ($recursive) {
                foreach ($this->children as $child) {
                    if (!$child->assign($roleName, $permissions, $recursive)) {
                        $trans->rollBack();
                        return false;
                    }
                }
            }
            $trans->commit();
            return true;
        } catch (\Exception $e) {
            $trans->rollBack();
            return false;
        }
    }

    /**
     * Отбирает указанные разрешения у роли
     * @param string $roleName для какой роли
     * @param string[]|string $permissions какие разрешения надо назначить
     * @param bool $recursive
     * @return bool
     */
    public function revoke($roleName, $permissions, $recursive = false)
    {
        $permissions = (array)$permissions;
        $trans = self::getDb()->beginTransaction();
        try {
            $exists = CategoryPermission::findAll(['roleName' => $roleName, 'categoryId' => $this->categoryId, 'permission' => $permissions]);

            foreach ($exists as $cp) {
                if (false === $cp->delete()) {
                    $trans->rollBack();
                    return false;
                }
            }

            if ($recursive) {
                foreach ($this->children as $child) {
                    if (!$child->revoke($roleName, $permissions, $recursive)) {
                        $trans->rollBack();
                        return false;
                    }
                }
            }
            $trans->commit();
            return true;
        } catch (\Exception $e) {
            $trans->rollBack();
            return false;
        }
    }

    /**
     * Заменяет разрешения у дочерней ветки категорий как у этой
     * @return bool
     */
    public function replaceChildrenPermission()
    {
        $trans = self::getDb()->beginTransaction();
        try {
            $permissions = $this->permissions;
            foreach ($this->children as $category) {
                if (!CategoryPermission::setPermissionRecursive($category, $permissions)) {
                    $trans->rollBack();
                    return false;
                }
            }
            $trans->commit();
            return true;
        } catch (\Exception $e) {
            $trans->rollBack();
            return false;
        }
    }

    /**
     * ищет категорию по ярлыку
     * @param string $slug ярлык
     * @return null|Category
     */
    public static function findBySlug($slug)
    {
        return static::findOne(['slug' => $slug]);
    }

    /**
     * выдает массив категорий которые доступны пользователю
     * @param Category|null $root корневая категория в потомках которой искать доступные подкатегории
     * если = null то берутся все категории
     * @param string[]|string $permissions какое разрешение надо проверять. если передан массив то должно быть
     * хотя бы одно из разрешений для того чтобы категория попала в результирующий список
     * @param Category[] $additional список категорий которые надо добавить в результат вывода
     * @return Category[]
     */
    public static function available($root, $permissions, $additional = null)
    {
        //загружаем данные категорий доступных пользователю с проверяемым разрешением
        $query = self::find()->distinct()
            ->joinWith('permissions')
            ->where(['permission' => $permissions, 'roleName' => ArrayHelper::getColumn(RbacHelper::rolesRecursiveForUser(), 'name')]);

        if ($additional !== null) {
            $additional = ArrayHelper::asArray($additional);
            $query->orWhere([self::tableName() . '.[[categoryId]]' => ArrayHelper::getColumn($additional, 'categoryId')]);
        }

        if ($root !== null) {
            //материализованныq путm для поиска потомков
            $query->andWhere(['like', 'mPath', $root->mPath . '^%', false]);
        }

        $raw = TreeHelper::build($query->asArray()->all(), 'categoryId', 'weight', SORT_ASC, false);

        return Helper::populateArray(Category::class, $raw);
    }

    // --------------------------------------------------- связи -----------------------------------------------------------

    /**
     * @return ActiveQuery
     */
    public function getPermissions()
    {
        return $this->hasMany(CategoryPermission::class, ['categoryId' => 'categoryId']);
    }

    /**
     * @return ActiveQuery
     */
    public function getPages()
    {
        return $this->hasMany(Page::class, ['pageId' => 'pageId'])->viaTable(PageInCategory::tableName(), ['categoryId' => 'categoryId']);
    }

    /**
     * @return ActiveQuery
     */
    public function getPagesBranch()
    {
        //определяем id подкатегорий дочерних веток
        $categories = self::find()
            ->select(['categoryId'])
            ->where(['or', ['like', 'mPath', $this->mPath . '^%', false], ['categoryId' => $this->categoryId]]);

        $query = Page::find()
            ->joinWith('pageInCategories')
            ->where(['in', 'categoryId', $categories]);
        $query->multiple = true;
        return $query;
    }
}
