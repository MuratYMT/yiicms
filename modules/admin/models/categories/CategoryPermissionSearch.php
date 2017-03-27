<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 28.08.2015
 * Time: 9:36
 */

namespace yiicms\modules\admin\models\categories;

use yii\base\Model;
use yii\data\ArrayDataProvider;
use yiicms\components\core\RbacHelper;
use yiicms\models\content\Category;
use yiicms\models\content\CategoryPermission;

class CategoryPermissionSearch extends Model
{
    public $roleName;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['roleName'], 'string', 'max' => 64],
        ];
    }

    /**
     * выдает матрицу разрешений ролей на категорию
     * @param Category $category
     * @param array $params параметры поиска
     * @return ArrayDataProvider
     */
    public function search($category, $params)
    {
        $roles = \Yii::$app->authManager->getRoles();
        $recursiveRoles = [];
        foreach ($roles as $roleName => $role) {
            if (!RbacHelper::isChild($role)) {
                RbacHelper::roleRecursive($recursiveRoles, $role);
            }
        }

        $table = [];
        foreach ($recursiveRoles as $role) {
            $row = CategoryPermission::permissionForRole($category, $role['name']);
            $row['roleName'] = $role['name'];
            $row['level'] = $role['level'];
            //правая дублированная колока в таблице (иначе поиск не работает)
            $row['roleName2'] = $row['roleName'];
            $table[] = $row;
        }

        if ($this->load($params) && $this->validate()) {
            $this->searchFilter($table);
        }

        $sortAttributes = CategoryPermission::$permissions;
        $sortAttributes[] = 'roleName';
        $sortAttributes[] = 'roleName2';

        $provider = new ArrayDataProvider([
            'allModels' => $table,
            'sort' => ['attributes' => $sortAttributes],
            'pagination' => false,
        ]);
        return $provider;
    }

    /**
     * выполняет фильтрацию строк при поиске
     * @param array $table
     */
    private function searchFilter(&$table)
    {
        if (!empty($this->roleName)) {
            foreach ($table as $key => $row) {
                if (mb_stripos($row['roleName'], $this->roleName) === false) {
                    unset($table[$key]);
                }
            }
        }
    }
}
