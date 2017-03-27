<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 13.03.2017
 * Time: 17:21
 */

namespace yiicms\modules\admin\models\roles;

use yii\base\Model;
use yii\db\Query;
use yii\rbac\DbManager;
use yii\rbac\Role;
use yiicms\components\core\validators\HtmlFilter;
use yiicms\components\core\validators\TitleValidator;

class RoleEdit extends Model
{
    public $name;
    public $description;
    /**
     * @var Role
     */
    public $role;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 64],
            [['name'], TitleValidator::class],
            [
                ['name'],
                function ($attribute) {
                    if ($this->role !== null) {
                        return;
                    }
                    /** @var DbManager $auth */
                    $auth = \Yii::$app->authManager;
                    $query = (new Query())
                        ->from($auth->itemTable)
                        ->where(['name' => $this->name]);
                    if ($query->count('*', $auth->db) > 0) {
                        $this->addError($attribute, \Yii::t('yiicms', 'Роль уже существует либо недопустимое имя роли'));
                    }
                },
            ],
            [['description'], 'string', 'max' => 100],
            [['description'], HtmlFilter::class],
        ];
    }

    /**
     * @return bool
     */
    public function save()
    {
        if (!$this->validate()) {
            return false;
        }

        /** @var DbManager $auth */
        $auth = \Yii::$app->authManager;

        if ($this->role === null) {
            $this->role = $auth->createRole($this->name);
            $this->role->description = $this->description;
            return $auth->add($this->role);
        } else {
            $oldName = $this->role->name;
            $this->role->name = $this->name;
            $this->role->description = $this->description;
            return $auth->update($oldName, $this->role);
        }
    }

    public static function findOne($roleName)
    {
        /** @var DbManager $auth */
        $auth = \Yii::$app->authManager;
        $role = $auth->getRole($roleName);
        if ($role === null) {
            return null;
        }
        return new RoleEdit(['name' => $role->name, 'description' => $role->description, 'role' => $role]);
    }
}
