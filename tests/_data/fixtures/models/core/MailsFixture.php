<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 23.05.2016
 * Time: 12:18
 */

namespace yiicms\tests\_data\fixtures\models\core;

use yiicms\models\core\Mails;
use yiicms\tests\_data\ActiveFixture;

class MailsFixture extends ActiveFixture
{
    public function __construct(array $config = [])
    {
        $this->modelClass = Mails::className();
        $this->depends = [UsersFixture::className()];
        parent::__construct($config);
    }
}
