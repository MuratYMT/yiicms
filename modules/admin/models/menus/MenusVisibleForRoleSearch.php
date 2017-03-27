<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 25.01.2016
 * Time: 9:20
 */

namespace yiicms\modules\admin\models\menus;

use yiicms\components\core\ArrayHelper;
use yiicms\models\core\Menus;
use yii\base\Model;
use yii\data\ArrayDataProvider;

class MenusVisibleForRoleSearch extends Model
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
     * @param Menus $menu для какого пункта меню производится поиск ролей которым предоставлен просмотр
     * @param array $params
     * @return \yii\data\ArrayDataProvider
     */
    public function search($menu, $params)
    {
        $table = [];

        foreach (\Yii::$app->authManager->getRoles() as $role) {
            $role = $role->name;
            $row['roleName'] = $role;
            $row['visible'] = in_array($role, ArrayHelper::getColumn($menu->menusForRole, 'roleName'), true);
            $table[] = $row;
        }

        if ($this->load($params) && $this->validate()) {
            $this->searchFilter($table);
        }

        $provider = new ArrayDataProvider([
            'allModels' => $table,
            'sort' => ['attributes' => ['roleName', 'visible']],
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
