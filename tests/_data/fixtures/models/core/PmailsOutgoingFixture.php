<?php
/**
 * Created by PhpStorm.
 * User: murat
 * Date: 04.12.2016
 * Time: 11:24
 */

namespace yiicms\tests\_data\fixtures\models\core;

use yiicms\models\core\PmailsOutgoing;
use yiicms\tests\_data\ActiveFixture;

class PmailsOutgoingFixture extends ActiveFixture
{
    public function __construct(array $config = [])
    {
        $this->modelClass = PmailsOutgoing::className();
        $this->depends = [UsersFixture::className(), PmailsFoldersFixture::className()];
        parent::__construct($config);
    }
}
