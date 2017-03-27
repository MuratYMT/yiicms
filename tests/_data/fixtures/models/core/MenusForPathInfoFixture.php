<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 03.06.2016
 * Time: 15:38
 */

namespace yiicms\tests\_data\fixtures\models\core;

use yiicms\models\core\MenusVisibleForPathInfo;
use yiicms\tests\_data\ActiveFixture;

class MenusForPathInfoFixture extends ActiveFixture
{
    public function __construct(array $config = [])
    {
        $this->modelClass = MenusVisibleForPathInfo::className();
        $this->depends = [UsersFixture::className(), MenusFixture::className()];
        parent::__construct($config);
    }
}
