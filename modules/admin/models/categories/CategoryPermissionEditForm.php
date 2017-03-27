<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 28.08.2015
 * Time: 10:37
 */

namespace yiicms\modules\admin\models\categories;

use yiicms\models\content\Category;
use yiicms\models\content\CategoryPermission;
use yii\base\InvalidParamException;
use yii\base\Model;

/**
 * Class CategoryPermissionEditForm
 * @package yiicms\modules\admin\models\pages
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
class CategoryPermissionEditForm extends Model
{

    /** @var Category Категория */
    public $category;
    /** @var string Роль */
    public $roleName;

    /**
     * @var bool Заменять разрешения для дочерних объектов
     */
    public $recursive;

    private $_permission = [];

    public function init()
    {
        parent::init();
        if ($this->category === null) {
            throw new InvalidParamException('Must be set ' . __CLASS__ . '::category before use');
        }
        if ($this->roleName === null) {
            throw new InvalidParamException('Must be set ' . __CLASS__ . '::roleName before use');
        }
    }

    public function rules()
    {
        return [
            [CategoryPermission::$permissions, 'required'],
            [CategoryPermission::$permissions, 'boolean'],
            [['recursive'], 'required'],
            [['recursive'], 'boolean'],
        ];
    }

    public function attributeLabels()
    {
        return array_merge(
            CategoryPermission::permissionLabels(),
            ['recursive' => \Yii::t('yiicms', 'Заменить разрешения для роли в дочерних категориях?')]
        );
    }

    public function save()
    {
        if (!$this->validate()) {
            return false;
        }

        $permissions = [];

        foreach (CategoryPermission::$permissions as $permission) {
            $permissions[$permission] = $this->$permission ? true : false;
        }

        return $this->category->setPermission($this->roleName, $permissions, $this->recursive);
    }

    /**
     * загружает разрешения из базы
     */
    protected function loadFromDb()
    {
        foreach (CategoryPermission::permissionForRole($this->category, $this->roleName) as $perm => $value) {
            $this->_permission[$perm] = $value;
        }
    }

    /**
     * Создает форму редактирования разрешений
     * @param Category $category Для какой категории
     * @param string $roleName Для какой роли
     * @return CategoryPermissionEditForm
     */
    public static function create($category, $roleName)
    {
        $model = new CategoryPermissionEditForm(['category' => $category, 'roleName' => $roleName]);
        $model->loadFromDb();
        return $model;
    }

    // ----------------------------- геттеры и сеттеры --------------------------------------------------------

    public function __get($name)
    {
        if (in_array($name, CategoryPermission::$permissions, true)) {
            return isset($this->_permission[$name]) ? $this->_permission[$name] : false;
        } else {
            return parent::__get($name);
        }
    }

    public function __set($name, $value)
    {
        if (in_array($name, CategoryPermission::$permissions, true)) {
            $this->_permission[$name] = $value;
        } else {
            parent::__set($name, $value);
        }
    }
}
