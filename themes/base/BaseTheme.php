<?php

/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 01.03.2017
 * Time: 11:39
 */

namespace yiicms\themes\base;

use yiicms\components\core\yii\Theme;

class BaseTheme extends Theme
{
    public static function themeTitle()
    {
        return \Yii::t('yiicms', 'Базовая тема');
    }

    public static function positions()
    {
        return [
            'topNavigation',
        ];
    }
}
