<?php
/**
 * Created by PhpStorm.
 * User: murat
 * Date: 04.12.2016
 * Time: 11:23
 */

namespace yiicms\tests\_data\fixtures\models\core;

use yiicms\models\core\PmailsIncoming;
use yiicms\tests\_data\ActiveFixture;

class PmailsIncomingFixture extends ActiveFixture
{
    public function __construct(array $config = [])
    {
        $this->modelClass = PmailsIncoming::className();
        $this->depends = [UsersFixture::className(), PmailsFoldersFixture::className(), PmailsOutgoingFixture::className()];
        parent::__construct($config);
    }
}
