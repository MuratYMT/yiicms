<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 16.09.2015
 * Time: 14:58
 */

namespace yiicms\modules\admin\models\users;

use yii\data\ArrayDataProvider;
use yii\rbac\Item;
use yiicms\components\core\ArrayHelper;

/**
 * Class PermissionSearch
 * @package yiicms\modules\admin\models\users
 */
class PermissionSearch extends CommonSearch
{
    public $name;
    public $description;
    public $role;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['name', 'description', 'role'], 'string', 'max' => 64],
        ];
    }

    /**
     * данные для таблицы всех разрешений.
     * выдает только разрешения назначенные ролям в которых состоит пользщователь.
     * дочерние разрешения разрешений игнорируются
     * @param array $params
     * @return ArrayDataProvider
     * @throws \yii\base\InvalidParamException
     */
    public function search($params)
    {
        $auth = \Yii::$app->authManager;
        $result = [];
        foreach ($auth->getAssignments($this->user->userId) as $assignment) {
            $role = $auth->getRole($assignment->roleName);
            self::permissionRecursive($result, $role);
        }

        if ($this->load($params) && $this->validate()) {
            $this->filter($result);
        }

        $provider = new ArrayDataProvider([
            'allModels' => $result,
            'sort' => [
                'attributes' => ['name', 'description', 'role'],
            ],
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
     * @param array $items
     */
    protected function filter(&$items)
    {
        if (!empty($this->name)) {
            foreach ($items as $key => $item) {
                if (mb_stripos($item['name'], $this->name) === false) {
                    unset($items[$key]);
                }
            }
        }

        if (!empty($this->role)) {
            foreach ($items as $key => $item) {
                if (mb_stripos($item['role'], $this->role) === false) {
                    unset($items[$key]);
                }
            }
        }

        if (!empty($this->description)) {
            foreach ($items as $key => $item) {
                if (mb_stripos($item['description'], $this->description) === false) {
                    unset($items[$key]);
                }
            }
        }
    }
}
