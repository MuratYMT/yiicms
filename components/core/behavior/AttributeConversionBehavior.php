<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 22.12.2016
 * Time: 14:58
 */

namespace yiicms\components\core\behavior;

use yii\base\Behavior;
use yii\db\BaseActiveRecord;

abstract class AttributeConversionBehavior extends Behavior
{
    /**
     * @var string[] список аттрибутов с которыми работает поведение
     */
    public $attributes = [];

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            BaseActiveRecord::EVENT_AFTER_UPDATE => 'afterFind',
            BaseActiveRecord::EVENT_AFTER_INSERT => 'afterFind',
            BaseActiveRecord::EVENT_AFTER_FIND => 'afterFind',
            BaseActiveRecord::EVENT_AFTER_REFRESH => 'afterFind',
            BaseActiveRecord::EVENT_BEFORE_UPDATE => 'beforeUpdate',
            BaseActiveRecord::EVENT_BEFORE_INSERT => 'beforeUpdate',
        ];
    }

    /**
     * вызывается для преобразования аттрибута из формата хранения
     */
    abstract public function afterFind();

    /**
     * вызывается для преобразования в формат хранения
     */
    abstract public function beforeUpdate();
}
