<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 25.05.2016
 * Time: 14:59
 */

namespace yiicms\tests\_data\fixtures\models\content;

use yiicms\models\content\Category;
use yiicms\tests\_data\ActiveFixture;
use yiicms\tests\_data\fixtures\models\core\RoleFixture;
use yiicms\tests\_data\fixtures\models\core\UsersFixture;

class CategoryFixture extends ActiveFixture
{
    public function __construct(array $config = [])
    {
        $this->modelClass = Category::className();
        $this->depends = [UsersFixture::className(), RoleFixture::className()];
        parent::__construct($config);
    }
}
