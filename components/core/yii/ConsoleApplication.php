<?php
/**
 * Created by PhpStorm.
 * User: murat
 * Date: 03.07.2015
 * Time: 22:22
 */

namespace yiicms\components\core\yii;

use yii\console\Application;

class ConsoleApplication extends Application
{
    use CommonApplicationTrait;

    protected function bootstrap()
    {
        parent::bootstrap();
        $this->registerUloadFolder();
    }
}
