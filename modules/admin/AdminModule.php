<?php
/**
 * Created by PhpStorm.
 * User: murat
 * Date: 08.03.2017
 * Time: 8:37
 */

namespace yiicms\modules\admin;

use yii\base\Module;
use yiicms\themes\admin\AdminTheme;

class AdminModule extends Module
{
    public function init()
    {
        parent::init();

        \Yii::$app->view->theme = new AdminTheme();
    }
}