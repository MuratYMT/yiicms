<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 25.11.2016
 * Time: 9:54
 */

namespace yiicms\tests\_data\fixtures\models\core;

use yiicms\models\core\Feedback;
use yiicms\tests\_data\ActiveFixture;

class FeedBackFixture extends ActiveFixture
{
    public function __construct(array $config = [])
    {
        $this->modelClass = Feedback::className();
        $this->depends = [UsersFixture::className()];
        parent::__construct($config);
    }
}
