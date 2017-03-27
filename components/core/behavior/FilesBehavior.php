<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 22.12.2016
 * Time: 16:54
 */

namespace yiicms\components\core\behavior;

use yiicms\components\core\File;

class FilesBehavior extends AttributeConversionBehavior
{
    public function afterFind()
    {
        foreach ($this->attributes as $modelField) {
            $this->owner->$modelField = File::createFromJson($this->owner->$modelField);
        }
    }

    public function beforeUpdate()
    {
        foreach ($this->attributes as $modelField) {
            $value = $this->owner->$modelField;
            $this->owner->$modelField = !empty($value) ? File::saveToJson($value) : null;
        }
    }
}