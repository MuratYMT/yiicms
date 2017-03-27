<?php

namespace yiicms\models\content;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "web.contentCategoriesPermissions".
 * @property integer $categoryId идентификатор узла
 * @property string $roleName роль
 * @property string $permission разрешение назначеное роли в этом узле
 * @property Category $category для какой категории это разрешение
 * @property bool $categoryView Просмотр содержимого категории;
 * @property bool $categoryCreate Создание дочерних категорий;
 * @property bool $pageRead Создание страниц;
 * @property bool $pageAdd Чтение страниц;
 * @property bool $pageEditOwn Редактирование своих страниц;
 * @property bool $pageEdit Редактирование любых страниц;
 * @property bool $pageDeleteOwn Удаление своих страниц;
 * @property bool $pageDelete Удаление любых страниц;
 * @property bool $pageCloseOwn Отключение комментариев на своих страницах;
 * @property bool $pageClose Отключение комментариев на любых страницах;
 * @property bool $commentAdd Комментирование;
 * @property bool $commentEditOwn Редактирование своих комментариев;
 * @property bool $commentEdit Редактирование любых комментариев;
 * @property bool $commentDeleteOwn Удаление своих комментариев;
 * @property bool $commentDelete Удаление любых комментариев;
 * @property bool $hideView Просмтр скрытого содержимого;
 * @property bool $historyView Просмотр истории изменений страницы;
 */
class CategoryPermission extends ActiveRecord
{
    /**
     * просмотр материалов категории
     */
    const CATEGORY_VIEW = 'categoryView';
    /**
     * создание подкатегорий
     */
    const CATEGORY_CREATE = 'categoryCreate';
    /**
     * чтение страниц в категории
     */
    const PAGE_READ = 'pageRead';
    /**
     * добавление страниц в категорию
     */
    const PAGE_ADD = 'pageAdd';
    /**
     * редактирование своих страниц в этой категории,
     * если страница находится в нескольких категориях то для редактирвоания,
     * надо обладать этим разрешением хотя бы в одной из категорий
     */
    const PAGE_EDIT_OWN = 'pageEditOwn';
    /**
     * редактирование любых старниц,
     * если страница находится в нескольких категориях то для редактирвоания,
     * надо обладать этим разрешением хотя бы в одной из категорий
     */
    const PAGE_EDIT = 'pageEdit';
    /**
     * удаление своих страниц в этой категории,
     */
    const PAGE_DELETE_OWN = 'pageDeleteOwn';
    /**
     * удаление любых страниц в этой категории
     */
    const PAGE_DELETE = 'pageDelete';
    /**
     * закрытие комментариев в своих страницах в этой категории
     */
    const PAGE_CLOSE_OWN = 'pageCloseOwn';
    /**
     * закрытие комментариев в любых страницах в этой категории
     */
    const PAGE_CLOSE = 'pageClose';
    /**
     * добавление комментариев к страницам в этой категории
     */
    const COMMENT_ADD = 'commentAdd';
    /**
     * редактирование своих комментариев к странице в этой категории
     */
    const COMMENT_EDIT_OWN = 'commentEditOwn';
    /**
     * редактирование любых комментариев к странице в этой категории
     */
    const COMMENT_EDIT = 'commentEdit';
    /**
     * удаление своих комментариев к странице в этой категории
     */
    const COMMENT_DELETE_OWN = 'commentDeleteOwn';
    /**
     * удаление любых комментариев к странице в этой категории
     */
    const COMMENT_DELETE = 'commentDelete';
    /**
     * просмотр скрытого содержимого
     */
    const HIDE_VIEW = 'hideView';
    /**
     * просмотр истории редактирования страниц в этой категории
     */
    const HISTORY_VIEW = 'historyView';

    public static $permissionTree = [
        self::CATEGORY_VIEW => [
            self::CATEGORY_CREATE,
            self::PAGE_READ => [
                self::PAGE_ADD => [
                    self::PAGE_EDIT_OWN,
                    self::PAGE_EDIT,

                    self::PAGE_DELETE_OWN,
                    self::PAGE_DELETE,

                    self::PAGE_CLOSE_OWN,
                    self::PAGE_CLOSE,
                ],

                self::COMMENT_ADD => [
                    self::COMMENT_EDIT_OWN,
                    self::COMMENT_EDIT,
                    self::COMMENT_DELETE_OWN,
                    self::COMMENT_DELETE,
                ],
                self::HISTORY_VIEW,
                self::HIDE_VIEW,
            ],
        ],
    ];

    /**
     * @var array доступные разрешения
     */
    public static $permissions = [
        self::CATEGORY_VIEW,
        self::CATEGORY_CREATE,
        self::PAGE_ADD,
        self::PAGE_READ,
        self::PAGE_EDIT_OWN,
        self::PAGE_EDIT,
        self::PAGE_DELETE_OWN,
        self::PAGE_DELETE,
        self::PAGE_CLOSE_OWN,
        self::PAGE_CLOSE,
        self::COMMENT_ADD,
        self::COMMENT_EDIT_OWN,
        self::COMMENT_EDIT,
        self::COMMENT_DELETE_OWN,
        self::COMMENT_DELETE,
        self::HIDE_VIEW,
        self::HISTORY_VIEW,
    ];

    /**
     * @var array разрешения которые требуют дополнительной проверки автора страницы
     */
    public static $permissionsPageOwn = [
        self::PAGE_EDIT,
        self::PAGE_DELETE,
        self::PAGE_CLOSE,
    ];

    /**
     * @var array разрешения которые требуют дополнительной проверки автора комментария
     */
    public static $permissionsCommentOwn = [
        self::COMMENT_EDIT,
        self::COMMENT_DELETE,
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%contentCategoriesPermissions}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['categoryId', 'roleName', 'permission'], 'required'],
            [['categoryId'], 'integer'],
            [['roleName'], 'string', 'max' => 64],
            [['permission'], 'string', 'max' => 50],
        ];
    }

    public function isTransactional($operation)
    {
        return true;
    }

    private $_permission = [];

    /**
     * @inheritDoc
     */
    public function __get($name)
    {
        if (in_array($name, self::$permissions, true)) {
            /** @noinspection NullCoalescingOperatorCanBeUsedInspection */
            return isset($this->_permission[$name]) ? $this->_permission[$name] : false;
        }
        return parent::__get($name);
    }

    /**
     * @inheritDoc
     */
    public function __set($name, $value)
    {
        if (in_array($name, self::$permissions, true)) {
            $this->_permission[$name] = $value;
        } else {
            parent::__set($name, $value);
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'categoryId' => \Yii::t('modules/content', 'Category ID'),
            'roleName' => \Yii::t('modules/content', 'Role Name'),
            'permission' => \Yii::t('modules/content', 'Permission'),
        ];
    }

    public function beforeSave($insert)
    {
        //дадим права из родительской ветки прав
        $compiledTree = self::compileParentsBranchTree(self::$permissionTree);
        /** @var array $parentPermissions */
        $parentPermissions = $compiledTree[$this->permission];

        $roleName = $this->roleName;
        $categoryId = $this->categoryId;
        foreach ($parentPermissions as $permission) {
            $model = self::findOne(['permission' => $permission, 'roleName' => $roleName, 'categoryId' => $categoryId]);
            if ($model === null) {
                $model = new self(['permission' => $permission, 'roleName' => $roleName, 'categoryId' => $categoryId]);
                $model->save();
            }
        }

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function insertInternal($attributes = null)
    {
        /**
         * проверяем наличие разрешения перед вставкой, потому что разрешение может уже быть добавлено ранее рекурсивным вызовом
         */
        $model = self::findOne(['permission' => $this->permission, 'roleName' => $this->roleName, 'categoryId' => $this->categoryId]);
        if ($model === null) {
            return parent::insertInternal($attributes);
        }
        return true;
    }

    public function beforeDelete()
    {
        //заберем права из дочерней ветки прав
        $compiledTree = self::compileChildrenBranchTree(self::$permissionTree);
        /** @var array $childrenPermissions */
        $childrenPermissions = $compiledTree[$this->permission];

        $roleName = $this->roleName;
        $categoryId = $this->categoryId;
        foreach ($childrenPermissions as $permission) {
            $model = self::findOne(['permission' => $permission, 'roleName' => $roleName, 'categoryId' => $categoryId]);
            if ($model !== null) {
                $model->delete();
            }
        }
        return parent::beforeDelete();
    }

    /**
     * название разрешения
     * @param string $permission идентификатор разрешения
     * если null то будет выдан массив всех названий разрешений
     * @return string|string[]
     */
    public static function permissionLabels($permission = null)
    {
        $labels = [
            self::CATEGORY_VIEW => \Yii::t('modules/content', 'Просмотр категории'),
            self::CATEGORY_CREATE => \Yii::t('modules/content', 'Создание подкатегорий'),
            self::PAGE_ADD => \Yii::t('modules/content', 'Создание страниц'),
            self::PAGE_READ => \Yii::t('modules/content', 'Чтение страниц'),
            self::PAGE_EDIT_OWN => \Yii::t('modules/content', 'Изменение своих страниц'),
            self::PAGE_EDIT => \Yii::t('modules/content', 'Изменение любых страниц'),
            self::PAGE_DELETE_OWN => \Yii::t('modules/content', 'Удаление своих страниц'),
            self::PAGE_DELETE => \Yii::t('modules/content', 'Удаление любых страниц'),
            self::PAGE_CLOSE_OWN => \Yii::t('modules/content', 'Отключение комментариев на своих страницах'),
            self::PAGE_CLOSE => \Yii::t('modules/content', 'Отключение комментариев на любых страницах'),
            self::COMMENT_ADD => \Yii::t('modules/content', 'Комментирование'),
            self::COMMENT_EDIT_OWN => \Yii::t('modules/content', 'Изменение своих комментариев'),
            self::COMMENT_EDIT => \Yii::t('modules/content', 'Изменение любых комментариев'),
            self::COMMENT_DELETE_OWN => \Yii::t('modules/content', 'Удаление своих комментариев'),
            self::COMMENT_DELETE => \Yii::t('modules/content', 'Удаление любых комментариев'),
            self::HIDE_VIEW => \Yii::t('modules/content', 'Просмтр скрытого содержимого'),
            self::HISTORY_VIEW => \Yii::t('modules/content', 'Просмотр истории изменений страницы'),
        ];
        if ($permission === null) {
            return $labels;
        }

        return isset($labels[$permission]) ? $labels[$permission] : $permission;
    }

    /**
     * выбает массив разрешений определенных в указанной категории для указанной роли
     * @param Category $category в какой категории
     * @param string $roleName для какой роли
     * @return array
     */
    public static function permissionForRole($category, $roleName)
    {
        $availablePerm = self::find()
            ->select(['permission'])
            ->where(['categoryId' => $category->categoryId, 'roleName' => $roleName])
            ->column(\Yii::$app->db);

        $result = [];
        foreach (self::$permissions as $permission) {
            $result[$permission] = in_array($permission, $availablePerm, true);
        }
        return $result;
    }

    /**
     * в каких узлах хотя бы у одной из $rolesNames есть хотя бы одно из $permissions
     * @param string|string[] $permissions
     * @param string|string[] $rolesNames
     * @return ActiveQuery
     */
    public static function categoriesWithPermissions($permissions, $rolesNames)
    {
        return Category::find()
            ->distinct()
            ->joinWith(['permissions'])
            ->andWhere(['permission' => $permissions, 'roleName' => $rolesNames]);
    }

    /**
     * Устанавливает разрешения у категории разрешения как указано. Все существующие разрешения удаляются
     * @param Category $category
     * @param CategoryPermission[] $permissions
     * @return bool
     */
    public static function setPermissionRecursive($category, $permissions)
    {
        $trans = self::getDb()->beginTransaction();
        try {
            self::getDb()->createCommand()
                ->delete(self::tableName(), ['categoryId' => $category->categoryId])
                ->execute();
            foreach ($permissions as $permission) {
                $model = new self([
                    'roleName' => $permission->roleName,
                    'permission' => $permission->permission,
                    'categoryId' => $category->categoryId,
                ]);
                if (!$model->save()) {
                    $trans->rollBack();
                    return false;
                }
            }
            foreach ($category->children as $child) {
                if (!self::setPermissionRecursive($child, $permissions)) {
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

    private static $_compiledParentsBranch;

    /**
     * Выпоняет компиляцию дерева разрешений в одномерный массив,
     * в котором ключи это идентификатор разрешения а значение массив идентификаторов родительской ветки в обратном порядке
     * @param array $tree исходное дерево разрешений
     * @return array скомпилированное дерево
     */
    public static function compileParentsBranchTree(array $tree)
    {
        if (self::$_compiledParentsBranch === null) {
            self::$_compiledParentsBranch = [];
            self::compileParentsBranchTreeRecursive($tree, self::$_compiledParentsBranch);
        }

        return self::$_compiledParentsBranch;
    }

    /**
     * Рекурсивная часть @see [[self::compileParentsBranchTree]]
     * @param array $tree исходное дерево разрешений
     * @param array $result скомпилированное дерево
     * @param array $treePath текущий путь от начала дерева в обратном порядке
     */
    private static function compileParentsBranchTreeRecursive(array $tree, array &$result, array $treePath = [])
    {
        $treePathOrigin = $treePath;
        foreach ($tree as $key => $value) {
            $treePath = $treePathOrigin;
            if (is_array($value)) {
                $result[$key] = $treePath;
                array_unshift($treePath, $key);
                self::compileParentsBranchTreeRecursive($value, $result, $treePath);
            } else {
                $result[$value] = $treePathOrigin;
            }
        }
    }

    private static $_compiledChildrenBranch;

    /**
     * Выпоняет компиляцию дерева разрешений в одномерный массив,
     * в котором ключи это идентификатор разрешения а значение массив идентификаторов дочерней ветки
     * @param array $tree исходное дерево разрешений
     * @return array скомпилированное дерево
     */
    public static function compileChildrenBranchTree(array $tree)
    {
        if (self::$_compiledChildrenBranch === null) {
            self::$_compiledChildrenBranch = [];
            self::compileChildrenBranchTreeRecursive($tree, self::$_compiledChildrenBranch);
        }
        return self::$_compiledChildrenBranch;
    }

    /**
     * Рекурсивная часть @see [[self::compileChildrenBranchTree]]
     * @param array $tree исходное дерево разрешений
     * @param array $result скомпилированное дерево
     * @param array $root текущий путь от начала дерева в обратном порядке
     * @return int[]
     */
    private static function compileChildrenBranchTreeRecursive(array $tree, &$result, $root = null)
    {
        $childrens = [];
        foreach ($tree as $key => $value) {
            if (is_array($value)) {
                $childrens[] = $key;
                /** @noinspection SlowArrayOperationsInLoopInspection */
                $childrens = array_merge($childrens, self::compileChildrenBranchTreeRecursive($value, $result, $key));
            } else {
                $result[$value] = [];
                $childrens[] = $value;
            }
        }

        if ($root !== null) {
            $result[$root] = $childrens;
        }
        return $childrens;
    }

    /**
     * используется при модульном тестировании
     */
    public static function clearCompiledTree()
    {
        self::$_compiledChildrenBranch = null;
        self::$_compiledParentsBranch = null;
    }

    // ---------------------------------------------------------- связи --------------------------------------------------------------------

    /**
     * @return ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(Category::class, ['categoryId' => 'categoryId']);
    }
}
