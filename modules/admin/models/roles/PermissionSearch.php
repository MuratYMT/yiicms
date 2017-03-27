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

class PermissionSearch extends Model
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
     * @param array $params параметры поиска в таблице
     * false - выдавать список разрешений назначенных этой роли (без назначенных в дочерних ролях)
     * true - выдавать список разрешений доступных роли включая назначенные в дочерних ролях
     * @return ArrayDataProvider
     */
    public function search($role, $params)
    {
        $auth = \Yii::$app->authManager;

        //список назначенных роли разрешений
        $assignments = [];
        foreach ($auth->getChildren($role->name) as $item) {
            if ((int)$item->type === Item::TYPE_PERMISSION) {
                $assignments[] = $item->name;
            }
        }

        $permissions = [];
        foreach ($auth->getPermissions() as $permission) {
            $permissions[] = [
                'name' => $permission->name,
                'description' => $permission->description,
                'assign' => in_array($permission->name, $assignments, true),
            ];
        }

        if ($this->load($params) && $this->validate()) {
            $this->filter($permissions);
        }

        $provider = new ArrayDataProvider([
            'allModels' => $permissions,
            'sort' => ['attributes' => ['name', 'description', 'assign']],
        ]);

        return $provider;
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
