<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 22.12.2016
 * Time: 14:41
 */

namespace yiicms\components\core\behavior;

/**
 * Class JsonArrayBehavior
 * @package yiicms\components\core\behavior
 */
class JsonArrayBehavior extends AttributeConversionBehavior
{
    public function afterFind()
    {
        foreach ($this->attributes as $modelField) {
            $this->owner->$modelField = json_decode($this->owner->$modelField, true);
        }
    }

    public function beforeUpdate()
    {
        foreach ($this->attributes as $modelField) {
            $this->owner->$modelField = json_encode($this->owner->$modelField, JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE);
        }
    }
}
