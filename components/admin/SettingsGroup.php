<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 02.08.2016
 * Time: 11:31
 */

namespace yiicms\components\admin;

use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\widgets\ActiveForm;
use yiicms\components\core\Helper;
use yiicms\components\core\SettingsBlock;
use yiicms\models\core\Settings;

/**
 * Class SettingsGroup
 * @package yiicms\components
 */
class SettingsGroup extends Model
{
    /** @var SettingsBlock блок настроек */
    public $settingsBlock;

    /** @var string группа настроек в модуле за которые отвечает эта модель */
    public $groupName = 'main';

    /** @var string заголовок группы настроек */
    public $groupTitle;

    /** @var string полное имя группы состоящее изимени_блока.group */
    public $group;

    /** @var array правила проверки */
    private $_rules = [];

    /** @var array массив аттрибутов которые являются массивами */
    protected $arrayAttributes = [];

    /** @var array массив аттрибутов */
    protected $allAttributes = [];

    public function init()
    {
        parent::init();

        if ($this->settingsBlock === null) {
            throw new InvalidConfigException('You must set property' . static::class . '::module before use');
        }

        $this->initAttributes();
    }

    public function rules()
    {
        return $this->_rules;
    }

    private $_attributeLabels = [];

    public function attributeLabels()
    {
        return $this->_attributeLabels;
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->allAttributes)) {
            $value = $this->allAttributes[$name];
            if (in_array($name, $this->arrayAttributes, true)) {
                $value = implode(', ', $value);
            }

            return $value;
        }
        return parent::__get($name);
    }

    public function __set($name, $value)
    {
        if (array_key_exists($name, $this->allAttributes)) {
            if (in_array($name, $this->arrayAttributes, true)) {
                $value = explode(', ', $value);
            }
            $this->allAttributes[$name] = $value;
        } else {
            parent::__set($name, $value);
        }
    }

    public function save()
    {
        if (!$this->validate()) {
            return false;
        }

        foreach ($this->allAttributes as $attribute => $value) {
            if (!Settings::set($this->group . '.' . $attribute, $value)) {
                return false;
            }
        }
        return true;
    }

    public static function render(ActiveForm $form, SettingsGroup $model, $group)
    {
        list ($block, $group) = explode('.', $group);

        $settingsBlock = Settings::getSettingsBlock($block);
        $method = 'render' . ucfirst($group) . 'Settings';
        echo $settingsBlock->$method($form, $model);
    }

    /**
     * инициализация модели
     */
    protected function initAttributes()
    {
        $settingsBlock = $this->settingsBlock;
        $settings = $settingsBlock->getSettings();
        $this->groupTitle = $settingsBlock->getSettingsGroupTitle($this->groupName);

        $this->group = lcfirst(Helper::classShortName($settingsBlock)) . '.' . $this->groupName;
        /** @noinspection ForeachSourceInspection */
        foreach ($settings[$this->groupName] as $attribute => $row) {

            $this->allAttributes[$attribute] = Settings::get($this->group . '.' . $attribute);
            $this->_attributeLabels[$attribute] = $row['title'];

            /** @noinspection ForeachSourceInspection */
            foreach ($row['rules'] as $rule) {
                $this->_rules[] = array_merge([$attribute], $rule);
            }

            if (is_array($row['value'])) {
                $this->arrayAttributes[] = $attribute;
            }
        }
    }

    // ---------------------------------------------- геттеры и сеттеры ----------------------------------------------------------
}
