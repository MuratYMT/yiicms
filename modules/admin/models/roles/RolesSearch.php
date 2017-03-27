<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 07.07.2015
 * Time: 15:56
 */

namespace yiicms\modules\admin\models\roles;

use yii\base\Model;
use yii\data\ArrayDataProvider;
use yii\db\Query;
use yii\rbac\DbManager;
use yii\rbac\Item;
use yii\rbac\Role;
use yiicms\components\core\RbacHelper;

class RolesSearch extends Model
{
    public $name;
    public $description;
    public $isGlobal;

    public function rules()
    {
        return [
            [['name', 'description'], 'string'],
            [['isGlobal'], 'boolean'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'parentsRolesNames' => \Yii::t('yiicms', 'Родительская роль'),
            'name' => \Yii::t('yiicms', 'Имя роли'),
            'description' => \Yii::t('yiicms', 'Описание'),
            'createdAt' => \Yii::t('yiicms', 'Дата создания'),
        ];
    }

    public function search($params)
    {
        $result = [];

        $roles = \Yii::$app->authManager->getRoles();
        foreach ($roles as $roleName => $role) {
            if (!RbacHelper::isChild($role)) {
                RbacHelper::roleRecursive($result, $role);
            }
        }

        if ($this->load($params) && $this->validate()) {
            $this->filter($result);
        }

        $provider = new ArrayDataProvider([
            'allModels' => $result,
            'pagination' => false,
        ]);
        return $provider;
    }

    /**
     * @param Role $role
     * @return ArrayDataProvider
     */
    public function searchChilds($role)
    {
        $childRoles = [];
        foreach (\Yii::$app->authManager->getChildren($role->name) as $item) {
            if ((int)$item->type === Item::TYPE_ROLE) {
                $childRoles[] = ['name' => $item->name, 'description' => $item->description];
            }
        }

        $provider = new ArrayDataProvider([
            'allModels' => $childRoles,
            'pagination' => false,
        ]);
        return $provider;
    }

    /**
     * @param array $roles
     */
    protected function filter(&$roles)
    {
        if (!empty($this->name)) {
            foreach ($roles as $key => $role) {
                if (mb_stripos($role['name'], $this->name) === false) {
                    unset($roles[$key]);
                }
            }
        }

        if (!empty($this->description)) {
            foreach ($roles as $key => $role) {
                if (mb_stripos($role['description'], $this->description) === false) {
                    unset($roles[$key]);
                }
            }
        }
    }
}
