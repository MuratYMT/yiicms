<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 31.05.2016
 * Time: 14:40
 */

namespace yiicms\components\admin;

use yii\bootstrap\Html;
use yii\bootstrap\InputWidget;
use yiicms\models\core\Menus;

class ParentMenuDropDown extends InputWidget
{
    public function init()
    {
        parent::init();
        if (!isset($this->options['encode'])) {
            $this->options['encode'] = false;
        }
    }

    public function run()
    {
        $result[''] = \Yii::t('modules/admin', 'БЕЗ РОДИТЕЛЬСКОГО');

        foreach (Menus::allMenus() as $menu) {
            $levelNod = (int)$menu->levelNod;
            if ((int)$levelNod === 1) {
                $result[$menu->menuId] = '&lt; ' . $menu->title . ' &gt;';
            } else {
                $result[$menu->menuId] = str_repeat('&nbsp;', ($levelNod - 1) * 6) . '|--' . $menu->title;
            }
        }

        $options = $this->options;

        if (!isset($options['class'])){
            $options['class'] = 'form-control';
        }

        return Html::activeDropDownList($this->model, $this->attribute, $result, $options);
    }
}
