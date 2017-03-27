<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 22.12.2016
 * Time: 9:08
 */

namespace yiicms\components\core\behavior;

use codemix\localeurls\UrlManager;
use yii\base\Model;
use yii\bootstrap\ActiveForm;
use yiicms\components\core\MultiLangHelper;
use yii\base\Behavior;
use yii\base\InvalidCallException;
use yii\db\BaseActiveRecord;

/**
 * Class MultiLangBehavior
 * @package yiicms\components\core\behavior
 * @property string $lang с каким языком по умолчанию из языкового массива на котором представлен объект должен работать объект
 */
class MultiLangBehavior2 extends Behavior
{
    /** @var string[] список multilang полей.
     * Key - имя аттрибута, value - [[rule1, rule2, ...], title]
     */
    public $attributes = [];

    /** @var string[] список multilang полей в базе данных (с добавленной M вконце поля) */
    protected $attributesM = [];

    protected $attributesExtendedRules = [];
    protected $attributesExtendedLabels = [];

    /** @var  string аттрибут который отвечает в модели за многоязыковой поиск */
    public $trgmIndex;

    public function init()
    {
        parent::init();
        /** @var UrlManager $urlManager */
        $urlManager = \Yii::$app->urlManager;
        /**
         * @var  string $attribute
         * @var  array $rules
         * @var  string $title
         */
        foreach ($this->attributes as $attribute => list($rules, $title)) {
            $this->attributesM[] = $attribute . 'M';
            foreach ($urlManager->languages as $language) {
                $att = $language . '__' . $attribute;
                $this->attributesExtendedLabels[$att] = "$title ($language)";

                foreach ($rules as $rule) {
                    array_unshift($rule, $att);
                    $this->attributesExtendedRules[] = $rule;
                }
            }
        }

        if ($this->trgmIndex === null) {
            if (count($this->attributes) === 1) {
                $attributes = array_keys($this->attributes);
                $this->trgmIndex = reset($attributes);
            } else {
                throw new InvalidCallException('Need set ' . self::class . '::trgmIndex');
            }
        }
    }

    /**
     * развернутые заголовки аттрибутов
     * @return array
     */
    public function attributeLabelsLang()
    {
        return $this->attributesExtendedLabels;
    }

    /**
     * развернутые правила аттрибутов
     * @return array
     */
    public function attributeRulesLang()
    {
        return $this->attributesExtendedRules;
    }

    /**
     * @param ActiveForm $activeForm
     * @param string $attribute
     * @return string
     */
    public function renderMultilang($activeForm, $attribute)
    {
        ob_start();
        /** @var UrlManager $urlManager */
        $urlManager = \Yii::$app->urlManager;
        /** @var Model $owner */
        $owner = $this->owner;
        foreach ($urlManager->languages as $language) {
            echo $activeForm->field($owner, $language . '__' . $attribute)->textInput();
        }
        return ob_get_clean();
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
        $fields = array_keys($this->attributes);
        foreach ($fields as $modelField) {
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
        $fields = array_keys($this->attributes);
        foreach ($fields as $modelField) {
            $modelFieldM = $modelField . 'M';
            $this->owner->$modelFieldM = MultiLangHelper::encodeLangArray($this->owner->$modelFieldM);
        }
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->attributes)) {
            $modelFiledM = $name . 'M';
            return MultiLangHelper::getValue($this->owner->$modelFiledM, $this->lang);
        } elseif (array_key_exists($name, $this->attributesExtendedLabels)) {
            list($lang, $attribute) = explode('__', $name);
            $modelFiledM = $attribute . 'M';
            return MultiLangHelper::getValue($this->owner->$modelFiledM, $lang);
        } else {
            return parent::__get($name);
        }
    }

    public function __set($name, $value)
    {
        if (array_key_exists($name, $this->attributes)) {
            $modelFiledM = $name . 'M';
            $multilang = $this->owner->$modelFiledM;
            $multilang[$this->lang] = $value;
            $this->owner->$modelFiledM = $multilang;
        } elseif (array_key_exists($name, $this->attributesExtendedLabels)) {
            list($lang, $attribute) = explode('__', $name);
            $modelFiledM = $attribute . 'M';
            $multilang = $this->owner->$modelFiledM;
            $multilang[$lang] = $value;
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
        if (array_key_exists($name, $this->attributesExtendedLabels) || array_key_exists($name, $this->attributes)) {
            return true;
        }
        return parent::canGetProperty($name, $checkVars);
    }

    /**
     * @inheritdoc
     */
    public function canSetProperty($name, $checkVars = true)
    {
        if (array_key_exists($name, $this->attributesExtendedLabels) || array_key_exists($name, $this->attributes)) {
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
