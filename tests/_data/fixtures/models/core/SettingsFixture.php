<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 24.11.2016
 * Time: 15:45
 */

namespace yiicms\tests\_data\fixtures\models\core;

use yiicms\models\core\Settings;
use yiicms\tests\_data\ActiveFixture;

class SettingsFixture extends ActiveFixture
{
    public function __construct(array $config = [])
    {
        $this->modelClass = Settings::className();
        parent::__construct($config);
    }
}
