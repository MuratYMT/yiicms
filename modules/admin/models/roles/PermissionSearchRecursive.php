<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 16.09.2015
 * Time: 11:01
 */

namespace yiicms\modules\admin\models\roles;

use yii\base\Model;
use yii\data\ArrayDataProvider;
use yii\rbac\Item;
use yii\rbac\Role;
use yiicms\components\core\ArrayHelper;

class PermissionSearchRecursive extends Model
{
    public $name;
    public $description;
    /** @var  string в какой роли назначено разрешение */
    public $role;

    public function rules()
    {
        return [
            [['name', 'description', 'role'], 'string', 'max' => 255],
        ];
    }

    /**
     * таблица разрешений роли
     * @param Role $role для какой роли ищем
     * @return ArrayDataProvider
     */
    public function search($role)
    {
        $permissions = [];
        self::permissionRecursive($permissions, $role);
        $provider = new ArrayDataProvider([
            'allModels' => $permissions,
            'sort' => ['attributes' => ['name', 'description', 'role']],
        ]);

        return $provider;
    }

    /**
     * рекурсивная функция определения разрешений назначенных роли
     * @param array $result
     * @param Item $role
     */
    private static function permissionRecursive(&$result, $role)
    {
        foreach (\Yii::$app->authManager->getChildren($role->name) as $item) {
            if ((int)$item->type === Item::TYPE_ROLE) {
                self::permissionRecursive($result, $item);
            } else {
                if (!isset($result[$item->name])) {
                    $result[$item->name] = ['name' => $item->name, 'description' => $item->description, 'role' => $role->name];
                } else {
                    $result[$item->name]['role'] = ArrayHelper::asArray($result[$item->name]['role']);
                    $result[$item->name]['role'][] = $role->name;
                }
            }
        }
    }

    /**
     * @param array $permissions
     */
    protected function filter(&$permissions)
    {
        if (!empty($this->name)) {
            foreach ($permissions as $key => $permission) {
                if (mb_stripos($permission['name'], $this->name) === false) {
                    unset($permissions[$key]);
                }
            }
        }

        if (!empty($this->role)) {
            foreach ($permissions as $key => $permission) {
                if (mb_stripos($permission['role'], $this->role) === false) {
                    unset($permissions[$key]);
                }
            }
        }

        if (!empty($this->description)) {
            foreach ($permissions as $key => $permission) {
                if (mb_stripos($permission['description'], $this->description) === false) {
                    unset($permissions[$key]);
                }
            }
        }
    }
}
