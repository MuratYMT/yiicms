<?php

namespace yiicms\models\core;

use yii\db\ActiveRecord;
use yii\rbac\Role;

/**
 * This is the model class for table "web.menusForRole".
 * @property integer $menuId
 * @property string $roleName
 * @property Menus $menu пунт меню
 * @property Role $role какая роль
 */
class MenusForRole extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%menusForRole}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['menuId', 'roleName'], 'required'],
            [['menuId'], 'integer'],
            [['roleName'], 'string', 'max' => 64],
            [
                ['roleName'],
                function ($attribute) {
                    if ($this->roleName === Settings::get('users.defaultGuestRole')) {
                        return;
                    }
                    $role = \Yii::$app->authManager->getRole($this->roleName);
                    if ($role === null) {
                        $this->addError($attribute, \Yii::t('yiicms', 'Неизвестная роль'));
                    }
                },
            ],
            [['menuId'], 'exist', 'targetClass' => Menus::class],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'menuId' => \Yii::t('yiicms', 'Menu ID'),
            'roleName' => \Yii::t('yiicms', 'Role Name'),
        ];
    }

    // ------------------------------------------------------ связи ---------------------------------------------------

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMenu()
    {
        return $this->hasOne(Menus::class, ['menuId' => 'menuId']);
    }

    // ---------------------------------------------- геттеры и сеттеры -----------------------------------------------

    public function getRole()
    {
        return \Yii::$app->authManager->getRole($this->roleName);
    }
}
