<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 03.09.2015
 * Time: 11:14
 */

namespace yiicms\components\core\fileicons;

use yii\web\AssetBundle;

class IconsAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public function __construct($config = [])
    {
        $class = new \ReflectionClass($this);
        $this->sourcePath = dirname($class->getFileName()) . '/icons';

        parent::__construct($config);
    }
}
