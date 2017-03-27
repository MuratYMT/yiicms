<?php
/**
 * Created by PhpStorm.
 * User: murat
 * Date: 22.03.2017
 * Time: 13:23
 */

namespace yiicms\modules\admin\models\pages;

use yiicms\models\content\Page;
use yiicms\models\core\LoadedFiles;

/**
 * Class PageEdit
 * @package yiicms\modules\admin\models\pages
 *
 * @property string[] $imagesIds Write Only.
 */
class PageEdit extends Page
{
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SC_EDIT][] = 'imagesIds';
        return $scenarios;
    }

    public function setImagesIds($value)
    {
        $result = [];
        foreach (LoadedFiles::findAll(['id' => $value]) as $image) {
            if ($image !== null) {
                $result[$image->id] = $image->file;
            }
        }
        $this->images = $result;
    }
}
