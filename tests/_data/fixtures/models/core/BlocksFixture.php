<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 24.11.2016
 * Time: 10:13
 */

namespace yiicms\tests\_data\fixtures\models\core;

use yiicms\models\core\Blocks;
use yiicms\tests\_data\ActiveFixture;

class BlocksFixture extends ActiveFixture
{
    public function __construct(array $config = [])
    {
        $this->modelClass = Blocks::className();
        $this->depends = [UsersFixture::className(), RoleFixture::className()];
        parent::__construct($config);
    }
}
