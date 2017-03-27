<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 30.03.2016
 * Time: 17:07
 */

namespace yiicms\components\core\rbac;

use yiicms\components\core\Helper;

abstract class Rule extends \yii\rbac\Rule
{
    public function init()
    {
        parent::init();
        if (empty($this->name)) {
            $this->name = Helper::classShortName(static::class);
        }
    }
}
