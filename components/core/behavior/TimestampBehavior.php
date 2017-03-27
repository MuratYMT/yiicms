<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 26.12.2016
 * Time: 9:28
 */

namespace yiicms\components\core\behavior;

use yiicms\components\core\DateTime;
use yii\base\Behavior;
use yii\base\Event;
use yii\db\BaseActiveRecord;

class TimestampBehavior extends Behavior
{

    /**
     * @var string[] список аттрибутов которые должны устанавливаться при создании модели
     */
    public $createdAttributes = [];

    /**
     * @var string[] список аттрибутов которые должны устанавливаться при обновлении модели
     */
    public $updatedAttributes = [];

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

    public function afterFind()
    {
        foreach ($this->updatedAttributes as $modelField) {
            $value = $this->owner->$modelField;
            if (!empty($value)) {
                $this->owner->$modelField = new DateTime($value, new \DateTimeZone('UTC'));
            }
        }
        foreach ($this->createdAttributes as $modelField) {
            $value = $this->owner->$modelField;
            if (!empty($value)) {
                $this->owner->$modelField = new DateTime($value, new \DateTimeZone('UTC'));
            }
        }
    }

    /**
     * @param Event $event
     */
    public function beforeUpdate($event)
    {

        $attributes = $event->name === BaseActiveRecord::EVENT_BEFORE_INSERT ? $this->createdAttributes : $this->updatedAttributes;

        foreach ($attributes as $modelField) {
            $this->owner->$modelField = DateTime::convertToDbFormat(DateTime::runTime(), null, DateTime::DATETIME_FORMAT);
        }
    }
}
