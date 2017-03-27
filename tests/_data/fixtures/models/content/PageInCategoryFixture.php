<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 25.05.2016
 * Time: 16:31
 */

namespace yiicms\tests\_data\fixtures\models\content;

use yiicms\models\content\PageInCategory;
use yiicms\tests\_data\ActiveFixture;
use yiicms\tests\_data\fixtures\models\core\UsersFixture;

class PageInCategoryFixture extends ActiveFixture
{
    public function __construct(array $config = [])
    {
        $this->modelClass = PageInCategory::className();
        $this->depends = [UsersFixture::className(), CategoryFixture::className(), PageFixture::className()];
        parent::__construct($config);
    }
}
