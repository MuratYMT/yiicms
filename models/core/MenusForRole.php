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

    /**
     * делает пункт меню видимым для роли
     * @param Menus $menu ID пункта меню
     * @param string $roleName для какой роли
     * @return bool
     */
    public static function grant($menu, $roleName)
    {
        $mfr = self::findOne(['roleName' => $roleName, 'menuId' => $menu->menuId]);
        if ($mfr !== null) {
            return true;
        }
        $mfr = new self(['roleName' => $roleName, 'menuId' => $menu->menuId]);
        return $mfr->save();
    }

    /**
     * делает пункт меню невидимым для роли
     * @param Menus $menu ID пункта меню
     * @param string $roleName для какой роли
     * @return bool
     */
    public static function revoke($menu, $roleName)
    {
        $mfr = self::findOne(['roleName' => $roleName, 'menuId' => $menu->menuId]);
        if ($mfr === null) {
            return true;
        }

        return false !== $mfr->delete();
    }

    /**
     * устанавливает видимость пукта меню для ролей как у родительского
     * @param Menus $menu
     * @return bool
     */
    public static function asParent($menu)
    {
        $menuId = $menu->menuId;
        if ($menu->parentId === 0) {
            return true;
        }
        self::getDb()->createCommand()
            ->delete(self::tableName(), ['menuId' => $menuId])
            ->execute();

        $mfrs = self::findAll(['menuId' => $menu->parentId]);
        foreach ($mfrs as $mfr) {
            $model = new self(['roleName' => $mfr->roleName, 'menuId' => $menuId]);
            if (!$model->save()) {
                return false;
            }
        }

        return true;
    }

    // ------------------------------------------------------ связи --------------------------------------------------------------

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMenu()
    {
        return $this->hasOne(Menus::class, ['menuId' => 'menuId']);
    }

    // ---------------------------------------------- геттеры и сеттеры -------------------------------------------------------------

    public function getRole()
    {
        return \Yii::$app->authManager->getRole($this->roleName);
    }
}
