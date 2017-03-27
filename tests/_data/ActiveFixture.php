<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 24.05.2016
 * Time: 10:58
 */

namespace yiicms\tests\_data;

class ActiveFixture extends \yii\test\ActiveFixture
{
    public function unload()
    {
        $this->resetTable();
        parent::unload();
    }
}
