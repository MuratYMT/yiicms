<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 02.06.2016
 * Time: 12:36
 */

namespace yiicms\tests\_data\fixtures\models\core;

use yiicms\models\core\Menus;
use yiicms\tests\_data\ActiveFixture;

class MenusFixture extends ActiveFixture
{
    public function __construct(array $config = [])
    {
        $this->modelClass = Menus::className();
        $this->depends = [UsersFixture::className(), RoleFixture::className()];
        parent::__construct($config);
    }
}
