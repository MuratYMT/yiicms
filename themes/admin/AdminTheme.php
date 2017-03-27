<?php
namespace yiicms\themes\admin;

use yiicms\components\core\yii\Theme;

/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 09.03.2017
 * Time: 8:50
 */
class AdminTheme extends Theme
{
    public static function themeTitle()
    {
        return \Yii::t('yiicms', 'Тема админки');
    }
}