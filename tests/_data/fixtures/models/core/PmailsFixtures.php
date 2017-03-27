<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 23.05.2016
 * Time: 12:18
 */

namespace yiicms\tests\_data\fixtures\models\core;

use yii\test\Fixture;

class PmailsFixtures extends Fixture
{
    public function __construct(array $config = [])
    {
        $this->depends = [
            PmailsFoldersFixture::className(),
            PmailsOutgoingFixture::className(),
            PmailsIncomingFixture::className(),
        ];
        parent::__construct($config);
    }
}
