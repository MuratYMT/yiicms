<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 25.05.2016
 * Time: 16:30
 */

namespace yiicms\tests\_data\fixtures\models\content;

use yiicms\models\content\Page;
use yiicms\tests\_data\ActiveFixture;
use yiicms\tests\_data\fixtures\models\core\UsersFixture;

class PageFixture extends ActiveFixture
{
    public function __construct(array $config = [])
    {
        $this->modelClass = Page::className();
        $this->depends = [
            UsersFixture::className(),
            CategoryFixture::className(),
            TagFixture::className(),
        ];
        parent::__construct($config);
    }
}
