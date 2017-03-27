<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 25.09.2015
 * Time: 10:56
 */

namespace yiicms\components\core\validators;

use yii\validators\FilterValidator;

class TitleFilter extends FilterValidator
{
    /**
     * @inheritDoc
     */
    public function init()
    {
        $this->filter = [self::class, 'titleFilter'];
        parent::init();
    }

    /**
     * фильтрует переданную строку оставляя в ней только буквы, цифры, пробелы, точки "_" и "-"
     * @param string $value
     * @return string
     */
    public static function titleFilter($value)
    {
        return trim(preg_replace('/[^\w \.\-]+/u', '', $value));
    }
}
