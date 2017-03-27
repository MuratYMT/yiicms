<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 26.05.2016
 * Time: 15:49
 */

namespace yiicms\tests\_data\fixtures\models\content;

use yiicms\models\content\Tag;
use yiicms\tests\_data\ActiveFixture;
use yiicms\tests\_data\fixtures\models\core\UsersFixture;

class TagFixture extends ActiveFixture
{
    public function __construct(array $config = [])
    {
        $this->modelClass = Tag::className();
        $this->depends = [UsersFixture::className()];
        parent::__construct($config);
    }
}
