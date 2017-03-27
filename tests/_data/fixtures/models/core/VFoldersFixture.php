<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 25.05.2016
 * Time: 8:37
 */

namespace yiicms\tests\_data\fixtures\models\core;

use yiicms\models\core\VFolders;
use yiicms\tests\_data\ActiveFixture;

class VFoldersFixture extends ActiveFixture
{
    public function __construct(array $config = [])
    {
        $this->modelClass = VFolders::className();
        $this->depends = [UsersFixture::className()];
        parent::__construct($config);
    }
}
