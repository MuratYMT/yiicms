<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 19.01.2016
 * Time: 9:30
 */

namespace yiicms\blocks\MenuBlock;

use yiicms\components\core\blocks\BlockEditor;
use yiicms\models\core\Menus;

/**
 * Class Editor
 * @package yiicms\blocks
 * @property $rootMenuId int идентификатор корневого узла меню для построения блока меню
 */
class WidgetEditor extends BlockEditor
{
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SC_EDIT][] = 'rootMenuId';

        return $scenarios;
    }

    /**
     * @return int|null
     */
    public function getRootMenuId()
    {
        if (array_key_exists('rootMenuId', $this->params)) {
            return $this->params['rootMenuId'];
        }

        return null;
    }

    /**
     * @param int $rootMenuId
     */
    public function setRootMenuId($rootMenuId)
    {
        $params = $this->params;
        $params['rootMenuId'] = $rootMenuId;
        $this->params = $params;
    }

    public function renderSpecificField($form)
    {
        echo $form->field($this, 'rootMenuId')->dropDownList($this->getMenuTree(), ['encode' => false]);
    }

    public function getMenuTree()
    {
        $menus = Menus::allMenus();

        $result = [];

        foreach ($menus as $menu) {
            $levelNod = $menu->levelNod;
            if ($levelNod === 1) {
                $result[$menu->menuId] = $menu->title;
            } else {
                $result[$menu->menuId] = str_repeat('&nbsp;', ($levelNod - 1) * 6) . '|--' . $menu->title;
            }
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(
            parent::attributeLabels(),
            [
                'rootMenuId' => \Yii::t('yiicms', 'Корневой узел меню'),
            ]
        );
    }
}
