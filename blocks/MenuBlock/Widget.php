<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 14.01.2016
 * Time: 14:18
 */

namespace yiicms\blocks\MenuBlock;

use yiicms\components\core\blocks\BlockWidget;
use yiicms\components\core\Helper;
use yiicms\components\core\TreeHelper;
use yiicms\components\YiiCms;
use yiicms\models\core\Menus;

/**
 * Class MenuBlockContentWidget
 * @package yiicms\blocks
 * @property $rootMenuId integer элемент меню являющийся корневым для этого блока меню
 */
class Widget extends BlockWidget
{

    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->title = \Yii::t('yiicms', 'Блок меню');
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $menuItems = YiiCms::$app->menuService->branchForUser(\Yii::$app->user->id, $this->rootMenuId);

        $rootMenu = Menus::findOne($this->rootMenuId);
        if ($rootMenu === null) {
            return '';
        }

        return $this->renderFile($this->viewFile, ['items' => self::convert($menuItems, $rootMenu->mPath . '^')]);
    }

    /**
     * выполняет конвертирование из линейного дерева в формат совместимый с yii\widgets\Menu
     * @param Menus[] $menus
     * @param string $mpathPrefix
     * @return array
     */
    private static function convert($menus, $mpathPrefix)
    {
        $result = [];
        foreach ($menus as $menu) {
            self::setValueRecursive($result, TreeHelper::mPath2ParentsIds(Helper::lTrimWord($menu->mPath, $mpathPrefix)), $menu);
        }
        return $result['items'];
    }

    /**
     * @param array $array
     * @param int[] $mpath
     * @param Menus $menu
     */
    private static function setValueRecursive(&$array, $mpath, $menu)
    {
        $item = (int)array_shift($mpath);
        if (count($mpath) === 0) {
            $ar = ['label' => $menu->title, 'url' => [$menu->link]];
            if (!empty($menu->icon)) {
                $ar['icon'] = 'fa ' . $menu->icon;
            }
            $array['items'][$item] = $ar;
        } else {
            self::setValueRecursive($array['items'][$item], $mpath, $menu);
        }
    }

    // ---------------------------------------------- геттеры и сеттеры ---------------------------------------------------------

    private $_rootMenuId;

    /**
     * @return mixed
     */
    public function getRootMenuId()
    {
        return $this->_rootMenuId;
    }

    /**
     * @param mixed $rootMenuId
     */
    public function setRootMenuId($rootMenuId)
    {
        $this->_rootMenuId = $rootMenuId;
    }
}
