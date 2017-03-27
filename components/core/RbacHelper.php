<?php
/**
 * Created by PhpStorm.
 * User: murat
 * Date: 22.03.2017
 * Time: 9:16
 */

namespace yiicms\components\core;

use yii\db\Query;
use yii\rbac\DbManager;
use yii\rbac\Item;
use yii\rbac\Role;
use yiicms\models\core\Settings;

class RbacHelper
{
    /**
     * Выдает список имен ролей назначенных пользователю и всех дочерних подролей
     * отсортированных в алфавитном порядке
     * @param int $userId Идентификатор пользователя. Если не указано то берется userId текущего пользователя
     * @return Role[]
     */
    public static function rolesRecursiveForUser($userId = null)
    {
        if ($userId === null) {
            $user = \Yii::$app->user;
            $userId = $user->isGuest ? 0 : (int)$user->id;
        }

        if ((int)$userId === 0) {
            return [Settings::get('users.defaultGuestRole')];
        }

        $auth = \Yii::$app->authManager;

        $userRoles = $auth->getRolesByUser($userId);
        foreach ($userRoles as $role) {
            /** @noinspection SlowArrayOperationsInLoopInspection */
            $userRoles = array_merge($userRoles, $auth->getChildRoles($role->name));
        }

        return $userRoles;
    }

    /**
     * определяет является ли роль дочерней
     * @param Role $role
     * @return bool true если есть родительская роль false если роль стоит в саммом верху и не имеет родителя
     */
    public static function isChild($role)
    {
        /** @var DbManager $auth */
        $auth = \Yii::$app->authManager;
        $query = (new Query())
            ->from($auth->itemChildTable)
            ->where(['child' => $role->name]);
        return $query->count('*', $auth->db) > 0;
    }

    /**
     * рекурсивная функция построения дерева ролей для роли указанной в качестве корневой
     * @param array $result результирующее дерево
     * @param Item $role для какой роли строится дерево (корневая роль)
     * @param int $level текущий уровень (используется в рекурсивных вызовах)
     * @param Role $parentRole родительская роль (используется в рекурсивных вызовах)
     */
    public static function roleRecursive(&$result, $role, $level = 1, $parentRole = null)
    {
        $result[] = [
            'name' => $role->name,
            'description' => $role->description,
            'level' => $level,
            'parentName' => $parentRole === null ? null : $parentRole->name,
            'createdAt' => $role->createdAt,
        ];
        foreach (\Yii::$app->authManager->getChildren($role->name) as $item) {
            if ((int)$item->type === Item::TYPE_ROLE) {
                self::roleRecursive($result, $item, $level + 1, $item);
            }
        }
    }
}