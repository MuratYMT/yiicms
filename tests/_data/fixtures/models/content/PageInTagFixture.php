<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 26.05.2016
 * Time: 16:30
 */

namespace yiicms\tests\_data\fixtures\models\content;

use yiicms\models\content\PageInTag;
use yiicms\tests\_data\ActiveFixture;
use yiicms\tests\_data\fixtures\models\core\UsersFixture;

class PageInTagFixture extends ActiveFixture
{
    public function __construct(array $config = [])
    {
        $this->modelClass = PageInTag::className();
        $this->depends = [UsersFixture::className(), PageFixture::className(), TagFixture::className()];
        parent::__construct($config);
    }
}
