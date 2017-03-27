<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 11.11.2016
 * Time: 11:40
 */

namespace yiicms\components\core\widgets;

use yii\base\InvalidCallException;
use yii\base\Model;
use yii\helpers\Html;

class UploadButton extends SubmitButton
{
    /** @var  Model */
    public $loadModel;

    public function init()
    {
        parent::init();
        if ($this->loadModel === null) {
            throw new InvalidCallException('Attribute ' . self::class . '::loadModel required');
        }
    }

    public function run()
    {
        if ($this->title === null) {
            $this->title = \Yii::t('yiicms', 'Загрузить');
        }
        $this->icon = 'upload';
        if (!isset($this->options['onclick'])) {
            $this->options['onclick'] =
                'this.disabled = true; $("#' . Html::getInputId($this->loadModel, 'uFiles[]') . '").fileinput("upload");return false;';
        }
        parent::run();
    }
}
