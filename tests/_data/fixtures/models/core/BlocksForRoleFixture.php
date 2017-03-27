<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 24.11.2016
 * Time: 10:28
 */

namespace yiicms\tests\_data\fixtures\models\core;

use yiicms\models\core\BlocksForRole;
use yiicms\tests\_data\ActiveFixture;

class BlocksForRoleFixture extends ActiveFixture
{
    public function __construct(array $config = [])
    {
        $this->modelClass = BlocksForRole::className();
        $this->depends = [UsersFixture::className(), RoleFixture::className(), BlocksFixture::className()];
        parent::__construct($config);
    }
}
