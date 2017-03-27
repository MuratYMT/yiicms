<?php

namespace yiicms\models\core;

/**
 * This is the model class for table "web.menusForPathInfo".
 * @property integer $permId ID правила
 * @property integer $menuId ID пункта меню для которого применяется это правило
 * @property string $rule правило обработки шаблона
 * contain - содержит
 * begins - начинается с
 * ends - заканчивается
 * equal - равен
 * pcre - значение шаблон PCRE
 * @property Menus $menu
 * @property string $template шаблон pathInfo
 */
class MenusVisibleForPathInfo extends VisibleForPathInfo
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%menusForPathInfo}}';
    }

    protected static function objectKey()
    {
        return 'menuId';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(
            parent::rules(),
            [
                [['menuId'], 'exist', 'targetClass' => Menus::class],
            ]
        );
    }

    // ------------------------------------------------------ связи ---------------------------------------------------------------

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMenu()
    {
        return $this->hasOne(Menus::class, ['menuId' => 'menuId']);
    }
}
