<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 23.06.2016
 * Time: 17:04
 */

namespace yiicms\tests\_data\fixtures\models\core;

use yiicms\models\core\Crontabs;
use yiicms\tests\_data\ActiveFixture;

class CrontabFixture extends ActiveFixture
{
    public function __construct(array $config = [])
    {
        $this->modelClass = Crontabs::className();
        parent::__construct($config);
    }
}
