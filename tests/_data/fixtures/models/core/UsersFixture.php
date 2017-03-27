<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 23.05.2016
 * Time: 10:42
 */

namespace yiicms\tests\_data\fixtures\models\core;

use yiicms\models\core\Users;
use yiicms\tests\_data\ActiveFixture;

class UsersFixture extends ActiveFixture
{
    public function __construct(array $config = [])
    {
        $this->modelClass = Users::className();
        parent::__construct($config);
    }
}
