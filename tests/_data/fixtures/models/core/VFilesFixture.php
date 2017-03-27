<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 25.05.2016
 * Time: 8:31
 */

namespace yiicms\tests\_data\fixtures\models\core;

use yiicms\models\core\VFiles;
use yiicms\tests\_data\ActiveFixture;

class VFilesFixture extends ActiveFixture
{
    public function __construct(array $config = [])
    {
        $this->modelClass = VFiles::className();
        $this->depends = [LoadedFilesFixture::className(), UsersFixture::className(), VFoldersFixture::className()];
        parent::__construct($config);
    }
}
