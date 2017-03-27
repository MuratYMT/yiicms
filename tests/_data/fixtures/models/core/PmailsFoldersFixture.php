<?php
/**
 * Created by PhpStorm.
 * User: murat
 * Date: 04.12.2016
 * Time: 11:22
 */

namespace yiicms\tests\_data\fixtures\models\core;

use yiicms\models\core\PmailsFolders;
use yiicms\tests\_data\ActiveFixture;

class PmailsFoldersFixture extends ActiveFixture
{
    public function __construct(array $config = [])
    {
        $this->modelClass = PmailsFolders::className();
        $this->depends = [UsersFixture::className()];
        parent::__construct($config);
    }
}
