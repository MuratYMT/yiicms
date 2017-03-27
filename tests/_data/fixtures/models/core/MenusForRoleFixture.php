<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 03.06.2016
 * Time: 10:00
 */

namespace yiicms\tests\_data\fixtures\models\core;

use yiicms\models\core\MenusForRole;
use yiicms\tests\_data\ActiveFixture;

class MenusForRoleFixture extends ActiveFixture
{
    public function __construct(array $config = [])
    {
        $this->modelClass = MenusForRole::className();
        $this->depends = [UsersFixture::className(), RoleFixture::className(), MenusFixture::className()];
        parent::__construct($config);
    }
}
