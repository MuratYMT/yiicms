<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 25.05.2016
 * Time: 15:12
 */

namespace yiicms\tests\_data\fixtures\models\content;

use yiicms\models\content\CategoryPermission;
use yiicms\tests\_data\ActiveFixture;
use yiicms\tests\_data\fixtures\models\core\RoleFixture;
use yiicms\tests\_data\fixtures\models\core\UsersFixture;

class CategoryPermissionFixture extends ActiveFixture
{
    public function __construct(array $config = [])
    {
        $this->modelClass = CategoryPermission::className();
        $this->depends = [UsersFixture::className(), RoleFixture::className(), CategoryFixture::className()];
        parent::__construct($config);
    }
}
