<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 11.04.2016
 * Time: 8:48
 */

namespace yiicms\models\core;

use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\db\Query;
use yii\rbac\DbManager;
use yii\rbac\Role;
use yiicms\components\core\ArrayHelper;

/**
 * Class RbacAssignment
 * @package yiicms\models\web
 * @property string[] $rolesNames список ролей назначенных пользователю
 * @property bool $isNew флаг того что новое назначение
 * @property Users $user
 */
class RbacAssignment extends Model
{
    const SC_ROLES_ADD = 'rolesAdd';
    const SC_ROLES_REMOVE = 'rolesRemove';

    /** @var  int */
    public $userId;

    public function init()
    {
        parent::init();

        if ($this->userId === null) {
            throw new InvalidConfigException(
                \Yii::t('yiicms', '{class}::userId должен быть задан', ['class' => static::class])
            );
        }
    }

    public function scenarios()
    {
        //дублировнаие необходимо, так как сценарии меняют поведение при присвоении аттрибута rolesNames
        return [
            self::SC_ROLES_REMOVE => ['rolesNames', '!userId'],
            self::SC_ROLES_ADD => ['rolesNames', '!userId'],
        ];
    }

    public function rules()
    {
        return [
            [['userId'], 'integer'],
            [
                ['userId'],
                function ($attribute) {
                    if (!$this->hasErrors() && !$this->checkUserForRule($this->$attribute)) {
                        $this->addError($attribute, \Yii::t('yiicms', 'Неизвестный пользователь'));
                    }
                },
            ],
            [
                ['rolesNames'],
                function ($attribute) {
                    if (!$this->hasErrors() && !$this->checkRoleForRule()) {
                        $this->addError($attribute, \Yii::t('yiicms', 'Неизвестная роль'));
                    }
                },
                'skipOnEmpty' => false,
            ],
        ];
    }

    /**
     * определяет допустимая ли роль для назначения
     * @return bool
     */
    protected function checkRoleForRule()
    {
        $roles = ArrayHelper::getColumn(RbacRoles::allRoles(), 'name');
        $roles[] = '0';
        foreach ($this->_rolesNames as $child) {
            if (!in_array($child, $roles, true)) {
                return false;
            }
        }
        return true;
    }

    protected function checkUserForRule($value)
    {
        return (null !== Users::findById($value));
    }

    /**
     * массив ролей назначенных пользователю
     * @param int $userId для какого пользователя. Если не указано то берется userId текущего пользователя
     * @return string[]
     */
    public static function rolesNamesForUser($userId = null)
    {
        if ($userId === null) {
            $user = \Yii::$app->user;
            $userId = $user->isGuest ? 0 : (int)$user->id;
        }

        if ((int)$userId === 0) {
            return [Settings::get('users.defaultGuestRole')];
        }

        return array_map(function ($n) {
            /** @var Role $n */
            return $n->name;
        }, \Yii::$app->authManager->getRolesByUser($userId));
    }

    /**
     * Выдает список имен ролей назначенных пользователю и всех дочерних подролей
     * отсортированных в алфавитном порядке
     * @param int $userId Идентификатор пользователя. Если не указано то берется userId текущего пользователя
     * @return string[]
     */
    public static function rolesNamesRecursiveForUser($userId = null)
    {
        if ($userId === null) {
            $user = \Yii::$app->user;
            $userId = $user->isGuest ? 0 : (int)$user->id;
        }

        if ((int)$userId === 0) {
            return [Settings::get('users.defaultGuestRole')];
        }

        /** @var \yii\rbac\DbManager $auth */
        $auth = \Yii::$app->authManager;

        $childrenList = RbacRoles::childrenList(Role::TYPE_ROLE);

        $assigmentRoles = (new Query())->select('item_name')
            ->from($auth->assignmentTable)
            ->where(['user_id' => $userId])
            ->column($auth->db);

        $allRoles = array_fill_keys($assigmentRoles, true);
        foreach ($assigmentRoles as $roleName) {
            self::getChildrenRecursive($roleName, $childrenList, $allRoles);
        }

        if (empty($allRoles)) {
            return [];
        }

        $roles = (new Query)
            ->select(['name'])
            ->from($auth->itemTable)->where([
                'type' => Role::TYPE_ROLE,
                'name' => array_keys($allRoles),
            ])
            ->column($auth->db);

        sort($roles, SORT_STRING);
        return $roles;
    }

    /**
     * Recursively finds all children and grand children of the specified item.
     * @param string $name the name of the item whose children are to be looked for.
     * @param array $childrenList the child list built via [[getChildrenList()]]
     * @param array $result the children and grand children (in array keys)
     */
    protected static function getChildrenRecursive($name, $childrenList, &$result)
    {
        if (isset($childrenList[$name])) {
            /** @noinspection ForeachSourceInspection */
            foreach ($childrenList[$name] as $child) {
                $result[$child['name']] = true;
                self::getChildrenRecursive($child['name'], $childrenList, $result);
            }
        }
    }

    /**
     * ищет объект RbacAssignment для указанного пользователя
     * @param $userId
     * @param array $config аттрибуты которые надо установить у объекта
     * @return null|static
     */
    public static function findOne($userId, array $config = [])
    {
        $user = Users::findById($userId);
        if ($user === null) {
            return null;
        }
        $config['userId'] = $user->userId;
        $model = new static($config);
        $model->_user = $user;
        $model->rolesNames = static::rolesNamesForUser($user->userId);
        $model->isNew = false;
        return $model;
    }

    public function save()
    {
        if (!$this->validate()) {
            return false;
        }

        $trans = self::getAuth()->db->beginTransaction();
        try {
            $this->updateAssignment();

            $trans->commit();
            $this->isNew = false;
            return true;
        } catch (\Exception $e) {
            $trans->rollBack();
            throw $e;
        }
    }

    protected function updateAssignment()
    {
        if ($this->_rolesNames === $this->_rolesNamesOld) {
            return;
        }

        $auth = self::getAuth();
        /**
         * @var int[] $added
         * @var int[] $removed
         */
        list ($added, $removed) = ArrayHelper::diffValues($this->_rolesNames, $this->_rolesNamesOld);

        foreach ($added as $roleName) {
            $role = $auth->getRole($roleName);
            if (null === $auth->getAssignment($roleName, $this->userId)) {
                $auth->assign($role, $this->userId);
            }
        }

        foreach ($removed as $roleName) {
            $role = $auth->getRole($roleName);
            $auth->revoke($role, $this->userId);
        }
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

    // ---------------------------------------------- геттеры и сеттеры ------------------------------------------------------

    /** @var  Users */
    private $_user;

    /**
     * @return Users
     */
    public function getUser()
    {
        if ($this->_user === null) {
            $this->_user = Users::findById($this->userId);
        }
        return $this->_user;
    }

    /**
     * @param Users $user
     */
    public function setUser($user)
    {
        $this->_user = $user;
    }

    private $_isNew = true;

    /**
     * @return boolean
     */
    public function getIsNew()
    {
        return $this->_isNew;
    }

    /**
     * @param boolean $isNew
     */
    public function setIsNew($isNew)
    {
        $this->_isNew = $isNew;
        if ($isNew) {
            $this->_rolesNamesOld = [];
        } else {
            $this->_rolesNamesOld = $this->_rolesNames;
        }
    }

    protected $_rolesNames = [];
    protected $_rolesNamesOld = [];

    /**
     * @return array
     */
    public function getRolesNames()
    {
        return $this->_rolesNames;
    }

    /**
     * @param array $rolesNames
     */
    public function setRolesNames($rolesNames)
    {
        $rolesNames = (array)$rolesNames;
        if ($this->scenario === self::SC_ROLES_ADD) {
            if ($this->_rolesNames === []) {
                $this->_rolesNames = $rolesNames;
            } else {
                $this->_rolesNames = array_merge($this->_rolesNames, $rolesNames);
            }
        } elseif ($this->scenario === self::SC_ROLES_REMOVE) {
            ArrayHelper::removeValues($this->_rolesNames, $rolesNames);
        } else {
            $this->_rolesNames = $rolesNames;
        }
    }
}
