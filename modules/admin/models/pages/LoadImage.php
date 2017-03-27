<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 30.09.2015
 * Time: 14:41
 */

namespace yiicms\modules\admin\models\pages;

use yiicms\components\core\FileLoadForm;
use yiicms\models\core\Settings;

class LoadImage extends FileLoadForm
{
    public static function allowedExtension()
    {
        return Settings::get('core.filemanager.imageFileExtension');
    }
}
