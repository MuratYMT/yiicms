<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 20.02.2017
 * Time: 15:04
 */

namespace yiicms\components\core;

use yii\base\Object;

abstract class SettingsBlock extends Object
{
    /**
     * массив настроек определенных в блоке
     * @return array
     */
    abstract  public function getSettings();

    /**
     * массив заголовков групп настроек определнных в блоке
     * @param string $name
     * @return \string[]
     */
    abstract public function getSettingsGroupTitle($name = null);
}