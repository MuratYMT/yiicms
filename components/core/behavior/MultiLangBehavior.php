<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 22.12.2016
 * Time: 9:08
 */

namespace yiicms\components\core\behavior;

use yiicms\components\core\MultiLangHelper;
use yii\base\Behavior;
use yii\base\InvalidCallException;
use yii\db\BaseActiveRecord;

/**
 * Class MultiLangBehavior
 * @package yiicms\components\core\behavior
 * @property string $lang с каким языком по умолчанию из языкового массива на котором представлен объект должен работать объект
 */
class MultiLangBehavior extends Behavior
{
    /** @var string[] список multilang полей */
    public $attributes = [];

    /** @var string[] список multilang полей в базе данных (с добавленной M вконце поля) */
    protected $attributesM = [];

    protected $attributesLang = [];

    /** @var  string аттрибут который отвечает в модели за многоязыковой поиск */
    public $trgmIndex;

    public function init()
    {
        parent::init();
        $this->attributesM = array_map(
            function ($n) {
                return $n . 'M';
            },
            $this->attributes
        );

        if ($this->trgmIndex === null) {
            if (count($this->attributes) === 1) {
                $this->trgmIndex = reset($this->attributes);
            } else {
                throw new InvalidCallException('Need set ' . self::class . '::trgmIndex');
            }
        }
    }

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
        foreach ($this->attributes as $modelField) {
            $modelFiledM = $modelField . 'M';
            $this->owner->$modelFiledM = MultiLangHelper::decodeLangArray($this->owner->$modelFiledM);
        }
    }

    public function beforeUpdate()
    {
        if ($this->trgmIndex !== false) {
            $modelFieldM = $this->trgmIndex . 'M';
            /** @noinspection PhpUndefinedFieldInspection */
            $this->owner->trgmIndex = implode('|', $this->owner->$modelFieldM);
        }
        foreach ($this->attributes as $modelField) {
            $modelFieldM = $modelField . 'M';
            $this->owner->$modelFieldM = MultiLangHelper::encodeLangArray($this->owner->$modelFieldM);
        }
    }

    public function __get($name)
    {
        if (in_array($name, $this->attributes, true)) {
            $modelFiledM = $name . 'M';
            return MultiLangHelper::getValue($this->owner->$modelFiledM, $this->lang);
        } else {
            return parent::__get($name);
        }
    }

    public function __set($name, $value)
    {
        if (in_array($name, $this->attributes, true)) {
            $modelFiledM = $name . 'M';
            $multilang = $this->owner->$modelFiledM;
            $multilang[$this->lang] = $value;
            $this->owner->$modelFiledM = $multilang;
        } else {
            parent::__set($name, $value);
        }
    }

    /**
     * @inheritdoc
     */
    public function canGetProperty($name, $checkVars = true)
    {
        if (in_array($name, $this->attributes, true)) {
            return true;
        }
        return parent::canGetProperty($name, $checkVars);
    }

    /**
     * @inheritdoc
     */
    public function canSetProperty($name, $checkVars = true)
    {
        if (in_array($name, $this->attributes, true)) {
            return true;
        }
        return parent::canSetProperty($name, $checkVars);
    }

    /**
     * определяет есть ли различия в языковых массивах
     * @param array $oldArray
     * @param array $newArray
     * @return bool
     */
    public static function equal(array $oldArray, array $newArray)
    {
        return array_diff_assoc($newArray, $oldArray) === [] && array_diff_assoc($oldArray, $newArray) === [];
    }

    // ------------------------------------------- геттеры и сеттеры -------------------------------------------------------

    /**
     * @var string на каком языке пункт меню (используется для массового присвоения или простого доступа к одному из вариантов языков)
     */
    private $_lang;

    /**
     * @return string
     */
    public function getLang()
    {
        if ($this->_lang === null) {
            $this->_lang = \Yii::$app->language;
        }
        return $this->_lang;
    }

    /**
     * @param string $lang
     */
    public function setLang($lang)
    {
        $this->_lang = $lang;
    }
}
