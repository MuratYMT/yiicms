<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 05.04.2016
 * Time: 12:52
 */

namespace yiicms\models\core;

use yiicms\components\core\ArrayHelper;
use yiicms\components\core\validators\HtmlFilter;
use yiicms\components\core\validators\TitleValidator;
use yii\base\InvalidCallException;
use yii\base\Model;
use yii\db\Query;
use yii\rbac\DbManager;
use yii\rbac\Item;
use yii\rbac\Role;

/**
 * Class RbacRoles
 * @package yiicms\models\web
 * @property string[] $childsRolesNames имена дочерних ролей
 * @property array $data дополнительные данные роли
 * @property string $name имя роли
 * @property string[] $permissionsNames список разрешений назначенных роли
 * @property array $permissions массив разрешений назначенных непосредственно этой роли. readOnly
 * @property array $permissionsRecursive массив всех разрешений (включая разрешения из дочерних ролей) назначенных роли. readOnly
 * @property $notAssigmentPermissions array массив разрешений не назначенных роли. readOnly
 * @property $menusForRole MenusForRole[]
 * @property $blocksForRole BlocksForRole[]
 * @property $role Role объект роли
 */
class RbacRoles extends Model
{
    const SC_EDIT = 'edit';
    const SC_ADD_CHILDS = 'addChilds';
    const SC_REMOVE_CHILDS = 'removeChilds';
    const SC_ADD_PERMISSION = 'addPermission';
    const SC_REMOVE_PERMISSION = 'removePermission';

    const UNDEFINED = '__UNDEFINED__';

    /** @var  string описание роли */
    public $description;

    /** @var array ополнительные данные */
    public $data = [];

    /** @var  int уровень в иерархии на котором находится этот элемент */
    public $level;

    /** @var bool флаг того что новая роль */
    public $isNew = true;

    /** @var bool флаг того что роль глобальная */
    public $isGlobal = true;

    public function __construct(array $config = [])
    {
        $this->_role = self::getAuth()->createRole(self::UNDEFINED);
        $this->isNew = true;
        parent::__construct($config);
    }

    public function scenarios()
    {
        //дублировнаие необходимо, так как сценарии меняют поведение при присвоении аттрибутов
        return [
            self::SCENARIO_DEFAULT => [],
            self::SC_EDIT => ['name', 'description'],
            self::SC_ADD_CHILDS => ['childsRolesNames'],
            self::SC_REMOVE_CHILDS => ['childsRolesNames'],
            self::SC_ADD_PERMISSION => ['permissionsNames'],
            self::SC_REMOVE_PERMISSION => ['permissionsNames'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 64],
            [['name'], TitleValidator::class],
            [['description'], 'string', 'max' => 100],
            [['description'], HtmlFilter::class],
            [
                ['childsRolesNames'],
                function ($attribute) {
                    if (!$this->hasErrors() && !$this->childsItemsForRules($this->_childsRolesNames)) {
                        $this->addError($attribute, \Yii::t('yiicms', 'Недопустимая дочерняя роль'));
                    }
                },
                'skipOnEmpty' => false,
            ],
            [
                ['permissionsNames'],
                function ($attribute) {
                    if (!$this->hasErrors() && !$this->permissionsForRules($this->_permissionsNames)) {
                        $this->addError($attribute, \Yii::t('yiicms', 'Недопустимое разрешение'));
                    }
                },
                'skipOnEmpty' => false,
            ],
        ];
    }

    /**
     * проверяет може ли указанное разрешение быть назначено этой роли
     * @param string[] $value
     * @return bool
     */
    protected function permissionsForRules($value)
    {
        $permissions = (new Query)
            ->select('name')
            ->from(self::getAuth()->itemTable)
            ->where(['type' => Item::TYPE_PERMISSION])
            ->column();

        foreach ($value as $permission) {
            if (!in_array($permission, $permissions, true)) {
                return false;
            }
        }
        return true;
    }

    /**
     * проверяет может ли указанная роль быть дочерней
     * @param string[] $value
     * @return bool
     */
    protected function childsItemsForRules($value)
    {
        $roles = ArrayHelper::getColumn(static::allRoles(), 'name');
        $roles[] = '0';
        foreach ($value as $child) {
            if (!in_array($child, $roles, true)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'parentsRolesNames' => \Yii::t('yiicms', 'Родительская роль'),
            'name' => \Yii::t('yiicms', 'Имя роли'),
            'description' => \Yii::t('yiicms', 'Описание'),
            'isGlobal' => \Yii::t('yiicms', 'Глобальная роль'),
        ];
    }

    /**
     * создает модель из базы данных
     * @param string $name имя роли
     * @param array $config дополнительные параметры инициализации объекта
     * @return null|static
     */
    public static function findOne($name, array $config = [])
    {
        $config['name'] = $name;
        $model = new static($config);
        return !$model->isNew ? $model : null;
    }

    public function save()
    {
        if (!$this->validate()) {
            return false;
        }
        $this->data['isGlobal'] = $this->isGlobal;

        $this->_role->data = $this->data;
        $this->_role->description = $this->description;

        $result = $this->isNew ? $this->insert() : $this->update();
        if ($result) {
            self::$_childrenListCash = [];
        }
        return $result;
    }

    protected function insert()
    {
        $auth = self::getAuth();
        $trans = $auth->db->beginTransaction();
        try {
            if (!$auth->add($this->_role)) {
                $trans->rollBack();
                return false;
            }

            if (!$this->updateChilds() || !$this->updatePermissions()) {
                $trans->rollBack();
                return false;
            }

            $trans->commit();
            $this->isNew = false;
            return true;
        } catch (\Exception $e) {
            $trans->rollBack();
            throw $e;
        }
    }

    protected function update()
    {
        $auth = self::getAuth();

        $trans = $auth->db->beginTransaction();
        try {
            if (!$this->updateChilds() || !$this->updatePermissions()) {
                $trans->rollBack();
                return false;
            }

            $auth->update($this->_nameOld !== null ? $this->_nameOld : $this->_role->name, $this->_role);

            $trans->commit();
            $this->isNew = false;
            return true;
        } catch (\Exception $e) {
            $trans->rollBack();
            throw $e;
        }
    }

    /**
     * удаляет роль
     * @return bool
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function delete()
    {
        $trans = self::getAuth()->db->beginTransaction();
        try {
            if (false !== ($result = $this->deleteInner())) {
                $trans->commit();
            } else {
                $trans->rollBack();
            }

            return $result;
        } catch (\Exception $e) {
            $trans->rollBack();
            throw $e;
        }
    }

    private function deleteInner()
    {
        foreach ($this->getMenusForRole() as $item) {
            if ($item->delete() === false) {
                return false;
            }
        }

        foreach ($this->getBlocksForRole() as $item) {
            if ($item->delete() === false) {
                return false;
            }
        }

        return self::getAuth()->remove($this->role);
    }

    /**
     * обновляет иерархию потомков
     * @return bool
     */
    private function updateChilds()
    {
        if ($this->_childsRolesNamesOld === $this->_childsRolesNames) {
            return true;
        }

        /**
         * @var int[] $added
         * @var int[] $removed
         */
        list($added, $removed) = ArrayHelper::diffValues($this->_childsRolesNames, $this->_childsRolesNamesOld);

        $auth = self::getAuth();

        foreach ($added as $name) {
            $role = $auth->getRole($name);
            if (!$this->setChild($this->_role, $role)) {
                return false;
            }
        }
        foreach ($removed as $name) {
            $role = $auth->getRole($name);
            $auth->removeChild($this->_role, $role);
        }

        if ($added !== [] || $removed !== []) {
            self::$_childrenListCash = [];
        }

        return true;
    }

    /**
     * обновляет назначенные разрешения
     * @return bool
     */
    private function updatePermissions()
    {
        if ($this->_permissionsNamesOld === $this->_permissionsNames) {
            return true;
        }

        /**
         * @var int[] $added
         * @var int[] $removed
         */
        list($added, $removed) = ArrayHelper::diffValues($this->_permissionsNames, $this->_permissionsNamesOld);

        $auth = self::getAuth();

        foreach ($added as $permissionName) {
            $permission = $auth->getPermission($permissionName);
            if (!$this->setChild($this->_role, $permission)) {
                return false;
            }
        }
        foreach ($removed as $permissionName) {
            $permission = $auth->getPermission($permissionName);
            $auth->removeChild($this->_role, $permission);
        }
        return true;
    }

    private function setChild($parentRole, $child)
    {
        /** @var DbManager $auth */
        $auth = self::getAuth();

        if (!$auth->hasChild($parentRole, $child)) {
            try {
                return $auth->addChild($parentRole, $child);
            } catch (InvalidCallException $e) {
                $this->addError('childsRolesNames', \Yii::t('yiicms', 'Нельзя добавить роль родительской ветки в качестве дочерней'));
            } catch (\Exception $e) {
                $this->addError('childsRolesNames', \Yii::t('yiicms', 'Ошибка добовления дочерней роли'));
            }
            return false;
        }
        return true;
    }

    /**
     * key-value массив всех ролей системы
     * @param array $config дополнительные данные конфигурации для ролей
     * @return RbacRoles[]
     */
    public static function allRoles(array $config = [])
    {
        $roles = [];
        static::getRoles(null, 1, $roles, true, $config);
        return $roles;
    }

    /**
     * массив разрешений назначенных пользователю
     * @param int $userId для какого пользователя
     * @return array
     */
    public static function permissionForUser($userId)
    {
        $permissions = [];
        $childrenList = self::childrenList();

        foreach (RbacAssignment::rolesNamesForUser($userId) as $role) {
            static::permissionForRole($role, $permissions, $childrenList, true);
        }

        return $permissions;
    }

    /**
     * формирует массив всех назначенных роли разрешений
     * @param string $name имя роли для которой формируется список
     * @param array $result результирующий массив разрешений
     * @param array $childrenList массив детей каждого элемента
     * @param bool $recutrsive собирать разрешения из вложенных ролей
     */
    private static function permissionForRole($name, &$result, &$childrenList, $recutrsive)
    {
        if (!isset($childrenList[$name])) {
            return;
        }

        /** @noinspection ForeachSourceInspection */
        foreach ($childrenList[$name] as $row) {
            if ($recutrsive && (int)$row['type'] === Item::TYPE_ROLE) {
                static::permissionForRole($row['name'], $result, $childrenList, $recutrsive);
            } elseif ((int)$row['type'] === Item::TYPE_PERMISSION) {
                $row['role'] = $name;
                $result[$row['name']] = $row;
            }
        }
    }

    /**
     * используется в рекурсивной функции построения иерархии, чтобы не передовать рекурсивно
     * @var array
     */
    private static $_childrenList = [];

    /**
     * формирирование линейного массива списка дочерних ролей
     * @param string $parentItemName для какого элемента строить рекурсию
     * @param int $level на каком уровне находится элемент
     * @param static[] $result результирующий линейный массив
     * @param bool $recursive получать вложенные роли
     * @param array $config дополнительные данные конфигурации для ролей
     */
    protected static function getRoles($parentItemName, $level, &$result, $recursive, array $config = [])
    {
        if ($level === 1) {
            self::$_childrenList = static::childrenList(Item::TYPE_ROLE);
        }

        $rows = self::childsLevelRoles($parentItemName);

        foreach ($rows as $row) {
            if (static::filterRoleFind($row)) {
                $config['name'] = $row['name'];
                $config['level'] = $level;
                $result[] = new static($config);
                if ($recursive) {
                    static::getRoles($row['name'], $level + 1, $result, $recursive, $config);
                }
            }
        }
    }

    /**
     * выполняет фильтрацию ролей
     * @param array $role массив данных роли
     * @return bool
     */
    protected static function filterRoleFind($role)
    {
        return true;
    }

    /**
     * список дочерних элементов
     * @param string $parentItemName для какого элемента выдавать список дочерних элементов
     * @return array
     */
    private static function childsLevelRoles($parentItemName)
    {
        $auth = self::getAuth();
        if ($parentItemName === null) {
            //подзапрос исключает элементы не верхнего уровня
            $subQuery = (new Query())
                ->select(['child'])->distinct()
                ->from($auth->itemChildTable);
            $rows = (new Query())
                ->select(['name'])
                ->from($auth->itemTable)
                ->where(['type' => Item::TYPE_ROLE])
                ->andWhere(['not in', 'name', $subQuery])
                ->orderBy('name')
                ->all();
        } else {
            /** @noinspection NullCoalescingOperatorCanBeUsedInspection */
            $rows = isset(self::$_childrenList[$parentItemName]) ? self::$_childrenList[$parentItemName] : [];
        }
        return $rows;
    }

    /** @var  DbManager */
    private static $_auth;

    /**
     * @return DbManager
     */
    protected static function getAuth()
    {
        if (self::$_auth === null) {
            self::$_auth = \Yii::$app->authManager;
        }
        return self::$_auth;
    }

    /** @var array */
    private static $_childrenListCash = [];

    /**
     * Returns the children for every parent.
     * @param int $type какие типы использовать для построения Item::TYPE_ROLE или Item::TYPE_PERMISSION
     * если не указано то используются все типы
     * @return array the children list. Each array key is a parent item name,
     * and the corresponding array value is a list of child item names.
     */
    public static function childrenList($type = null)
    {
        if (!isset(self::$_childrenListCash[$type])) {
            $auth = self::getAuth();
            $query = (new Query())
                ->select(['i.*', 'ic.parent'])
                ->from(['ic' => $auth->itemChildTable])
                ->rightJoin(['i' => $auth->itemTable], 'ic.child = i.name')
                ->orderBy('i.name');

            if ($type !== null) {
                $query->where('i.type = :type', [':type' => $type]);
            }

            $parents = [];
            foreach ($query->all($auth->db) as $row) {
                $parents[$row['parent']][] = $row;
            }
            self::$_childrenListCash[$type] = $parents;
        }
        return self::$_childrenListCash[$type];
    }

    /**
     * массив непосредственных ролей - потомков
     * @param array $config дополнительные данные конфигурации для ролей
     * @return static[]
     */
    public function childrenRoles(array $config = [])
    {
        $roles = [];
        static::getRoles($this->name, 1, $roles, false, $config);
        return $roles;
    }

    // ------------------------------------- геттеры и сеттеры ---------------------------------------------------------------------------------------

    public function getName()
    {
        if ($this->_role->name === self::UNDEFINED) {
            return null;
        }
        return $this->_role->name;
    }

    private $_nameOld;

    public function setName($name)
    {
        $auth = self::getAuth();

        if ($this->_role->name === self::UNDEFINED) {
            $role = $auth->getRole($name);
            if ($role !== null) {
                $this->role = $role;
                $this->isNew = false;
                $this->description = $role->description;
                $this->data = $role->data;
                $this->isGlobal = isset($this->data['isGlobal']) && $this->data['isGlobal'];
            }
        }

        if ($this->_role->name !== $name) {
            $this->_nameOld = $this->_role->name;
            $this->_role->name = $name;
        }
    }

    /** @var  Role */
    private $_role;

    public function getRole()
    {
        return $this->_role;
    }

    /**
     * @param Role $role
     */
    public function setRole($role)
    {
        $this->_role = $role;

        $this->_childsRolesNames = ArrayHelper::getColumn($this->childrenRoles(), 'name');
        $this->_childsRolesNamesOld = $this->_childsRolesNames;
        $this->_permissionsNames = ArrayHelper::getColumn($this->permissions, 'name');
        $this->_permissionsNamesOld = $this->_permissionsNames;
    }

    /** @var bool|string[] */
    protected $_childsRolesNames = [];
    protected $_childsRolesNamesOld = [];

    /**
     * @return string
     */
    public function getChildsRolesNames()
    {
        return $this->_childsRolesNames;
    }

    /**
     * @param mixed $childsRolesNames
     */
    public function setChildsRolesNames($childsRolesNames)
    {
        $childsRolesNames = (array)$childsRolesNames;
        if ($this->scenario === self::SC_ADD_CHILDS) {
            if ($this->_childsRolesNames === []) {
                $this->_childsRolesNames = $childsRolesNames;
            } else {
                $this->_childsRolesNames = array_merge($this->_childsRolesNames, $childsRolesNames);
            }
        } elseif ($this->scenario === self::SC_REMOVE_CHILDS && $this->_childsRolesNames !== []) {
            ArrayHelper::removeValues($this->_childsRolesNames, $childsRolesNames);
        } else {
            $this->_childsRolesNames = $childsRolesNames;
        }
    }

    /** @var bool|string[] */
    protected $_permissionsNames = [];
    protected $_permissionsNamesOld = [];

    /**
     * @param mixed $permissionsNames
     */
    public function setPermissionsNames($permissionsNames)
    {
        $permissionsNames = (array)$permissionsNames;
        if ($this->scenario === self::SC_ADD_PERMISSION) {
            if ($this->_permissionsNames === []) {
                $this->_permissionsNames = $permissionsNames;
            } else {
                $this->_permissionsNames = array_merge($this->_permissionsNames, $permissionsNames);
            }
        } elseif ($this->scenario === self::SC_REMOVE_PERMISSION && $this->_permissionsNames !== []) {
            ArrayHelper::removeValues($this->_permissionsNames, $permissionsNames, true);
        } else {
            $this->_permissionsNames = $permissionsNames;
        }
    }

    public function getPermissionsNames()
    {
        return $this->_permissionsNames;
    }

    public function getPermissionsRecursive()
    {
        $permissions = [];
        $childrenList = static::childrenList();
        static::permissionForRole($this->name, $permissions, $childrenList, true);

        return $permissions;
    }

    public function getPermissions()
    {
        $permissions = [];
        $childrenList = static::childrenList(Item::TYPE_PERMISSION);
        static::permissionForRole($this->name, $permissions, $childrenList, false);

        return $permissions;
    }

    public function getNotAssigmentPermissions()
    {
        $auth = self::getAuth();
        return (new Query())
            ->select('*')
            ->from([$auth->itemTable])
            ->where(['not in', 'name', $this->_permissionsNames])
            ->andWhere(['type' => Item::TYPE_PERMISSION])
            ->all();
    }

    public function getMenusForRole()
    {
        return MenusForRole::find()->where(['roleName' => $this->name])->all();
    }

    public function getBlocksForRole()
    {
        return BlocksForRole::find()->where(['roleName' => $this->name])->all();
    }

    /**
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->_role->createdAt;
    }
}
