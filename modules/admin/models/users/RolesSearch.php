<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 16.09.2015
 * Time: 16:55
 */

namespace yiicms\modules\admin\models\users;

use yii\data\ArrayDataProvider;
use yii\db\Query;
use yii\rbac\DbManager;
use yii\rbac\Item;
use yii\rbac\Role;
use yiicms\components\core\ArrayHelper;

class RolesSearch extends CommonSearch
{
    public $name;
    public $description;
    public $assign;

    public function rules()
    {
        return [
            [['name', 'description'], 'string', 'max' => 64],
            [['assign'], 'boolean'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => \Yii::t('yiicms', 'Роль'),
            'description' => \Yii::t('yiicms', 'Описание роли'),
            'assign' => \Yii::t('yiicms', 'Назначено'),
        ];
    }

    /**
     * данные для таблицы назначенных ролей
     * выдает роли которые назначены пользователю
     * @param array $params
     * @return ArrayDataProvider
     */
    public function search($params)
    {
        $auth = \Yii::$app->authManager;
        $assignments = ArrayHelper::getColumn($auth->getAssignments($this->user->userId), 'roleName');
        $allRoles = $auth->getRoles();

        $result = [];
        foreach ($allRoles as $roleName => $role) {
            if (!self::isChild($role)) {
                self::roleRecursive($result, $role, 1, $assignments);
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
     * рекурсивная функция определения разрешений назначенных роли
     * @param array $result
     * @param Item $role
     * @param int $level
     * @param string[] $assignments
     */
    private static function roleRecursive(&$result, $role, $level, $assignments)
    {
        $result[] = [
            'name' => $role->name,
            'description' => $role->description,
            'level' => $level,
            'assign' => in_array($role->name, $assignments, true),
            'createdAt' => $role->createdAt,
        ];
        foreach (\Yii::$app->authManager->getChildren($role->name) as $item) {
            if ((int)$item->type === Item::TYPE_ROLE) {
                self::roleRecursive($result, $item, $level + 1, $assignments);
            }
        }
    }

    /**
     * определяет является ли роль дочерней
     * @param Role $role
     * @return bool
     */
    private static function isChild($role)
    {
        /** @var DbManager $auth */
        $auth = \Yii::$app->authManager;
        $query = (new Query())
            ->from($auth->itemChildTable)
            ->where(['child' => $role->name]);
        return $query->count('*', $auth->db) > 0;
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
        /*$isGlobal = (bool)$this->isGlobal;

        if ($this->isGlobal !== '' && $this->isGlobal !== null) {
            foreach ($roles as $key => $role) {
                if ($isGlobal !== $role->isGlobal) {
                    unset($roles[$key]);
                }
            }
        }*/
    }
}
