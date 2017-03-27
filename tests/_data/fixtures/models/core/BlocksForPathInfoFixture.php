<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 24.11.2016
 * Time: 10:29
 */

namespace yiicms\tests\_data\fixtures\models\core;

use yiicms\models\core\BlocksVisibleForPathInfo;
use yiicms\tests\_data\ActiveFixture;

class BlocksForPathInfoFixture extends ActiveFixture
{
    public function __construct(array $config = [])
    {
        $this->modelClass = BlocksVisibleForPathInfo::className();
        $this->depends = [UsersFixture::className(), BlocksFixture::className()];
        parent::__construct($config);
    }
}
