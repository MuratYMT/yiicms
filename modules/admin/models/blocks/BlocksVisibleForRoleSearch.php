<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 21.01.2016
 * Time: 8:18
 */

namespace yiicms\modules\admin\models\blocks;

use yiicms\models\core\Blocks;
use yii\base\Model;
use yii\data\ArrayDataProvider;

class BlocksVisibleForRoleSearch extends Model
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
     * @param Blocks $block для какого блока производится поиск ролей которым предоставлен просмотр
     * @param $params
     * @return \yii\data\ArrayDataProvider
     */
    public function search($block, $params)
    {
        $table = [];

        foreach (\Yii::$app->authManager->getRoles() as $role) {
            $role = $role->name;
            $row['roleName'] = $role;
            $row['visible'] = in_array($role, $block->visibleForRole, true);
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
