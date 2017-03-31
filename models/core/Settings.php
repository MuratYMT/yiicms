<?php

namespace yiicms\models\core;

use Yii;
use yii\base\InvalidParamException;
use yii\db\ActiveRecord;
use yii\helpers\Inflector;
use yiicms\components\core\behavior\JsonArrayBehavior;
use yiicms\components\core\SettingsBlock;
use yiicms\components\core\yii\CommonApplicationTrait;

/**
 * This is the model class for table "web.settings".
 * @property string $paramName Имя настройки
 * @property mixed $value Значение
 */
class Settings extends ActiveRecord
{
    /** @var string[] папки где хранятся настройки сайта доступные к изменению через web интерфейс */
    public $folders;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%settings}}';
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            [
                'class' => JsonArrayBehavior::class,
                'attributes' => ['value'],
            ],
        ]);
    }

    public function init()
    {
        parent::init();
        if ($this->folders === null) {
            $this->folders = ['@yiicms/common/settings'];
        } elseif (is_string($this->folders)) {
            $this->folders = [$this->folders];
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['paramName'], 'required'],
            [['value'], 'safe'],
            [['paramName'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'paramName' => Yii::t('yiicms', 'Имя настройки'),
            'value' => Yii::t('yiicms', 'Значение'),
        ];
    }

    private static $_params = [];

    /**
     * выдает значение параметра
     * @param string $name имя параметра
     * @return mixed значение параметра
     */
    public static function get($name)
    {
        list($blockName, $group, $param) = self::exlodeName($name);
        $name = $blockName . '.' . $group . '.' . $param;

        if (!isset(self::$_params[$name])) {
            $paramModel = self::findOne(['paramName' => $name]);
            if ($paramModel !== null) {
                self::$_params[$name] = $paramModel->value;
            } else {
                $settingsBlock = self::getSettingsBlock($blockName);

                $settings = $settingsBlock->getSettings();
                if (!isset($settings[$group][$param])) {
                    throw new InvalidParamException("Unknown settings $group.$param in module $blockName");
                }

                self::$_params[$name] = $settings[$group][$param]['value'];
            }
        }

        return self::$_params[$name];
    }

    /**
     * устанавливает значение параметра
     * @param string $name
     * @param mixed $value
     * @return bool
     */
    public static function set($name, $value)
    {
        list($blockName, $group, $param) = self::exlodeName($name);
        $name = $blockName . '.' . $group . '.' . $param;

        $settingsBlock = self::getSettingsBlock($blockName);

        $settings = $settingsBlock->getSettings();
        if (!isset($settings[$group][$param])) {
            throw new InvalidParamException("Unknown settings $group.$param in module $blockName");
        }

        $paramModel = self::findOne(['paramName' => $name]);
        if ($paramModel === null) {
            $paramModel = new self(['paramName' => $name]);
        }
        $paramModel->value = $value;
        if ($paramModel->save()) {
            self::$_params[$name] = $value;
            return true;
        }

        return false;
    }

    /**
     * @return SettingsBlock[]
     */
    public static function scanBlocks()
    {
        $result = [];
        /** @var CommonApplicationTrait $app */
        $app = Yii::$app;

        /** @var string[] $namespacess */
        $namespacess = is_string($app->settingsNamespaces) ? [$app->settingsNamespaces] : $app->settingsNamespaces;

        foreach ($namespacess as $nameSpace) {
            $folder = Yii::getAlias('@' . str_replace('\\', '/', $nameSpace));
            $files = scandir($folder);
            foreach ($files as $file) {
                $f = $folder . DIRECTORY_SEPARATOR . $file;
                /** @noinspection NotOptimalIfConditionsInspection */
                if (!is_dir($f) && $file !== '..' && $file !== '.') {
                    $class = $nameSpace . '\\' . pathinfo($file, PATHINFO_FILENAME);
                    if (class_exists($class) && is_subclass_of($class, SettingsBlock::class)) {
                        $result[] = new $class;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @param string $blockName ID модуля
     * @param string $param название параметра
     * @return string
     */
    public static function normalizeName($blockName, $param)
    {
        if (strpos($param, '-') === false) {
            $group = 'main';
            $attribute = $param;
        } else {
            list ($group, $attribute) = explode('-', $param);
        }

        return $blockName . '.' . $group . '-' . $attribute;
    }

    /**
     * @param string $name
     * @return array
     */
    private static function exlodeName($name)
    {
        $ar = explode('.', $name);
        if (!is_array($ar)) {
            throw new InvalidParamException("Bad settings name \"$name\"");
        }
        if (count($ar) === 2) {
            list($blockName, $param) = $ar;
            $group = 'main';
        } elseif (count($ar) === 3) {
            list($blockName, $group, $param) = $ar;
        } else {
            throw new InvalidParamException("Get unknown settings $name");
        }

        if (self::getSettingsBlockClass($blockName) === false) {
            throw new InvalidParamException("Get settings $name of an unknown block");
        }

        return [$blockName, $group, $param];
    }

    /**
     * проверяет наличие блока настроек
     * @param string $blockName имя блока настроек
     * @return bool|string
     */
    private static function getSettingsBlockClass($blockName)
    {
        /** @var CommonApplicationTrait $app */
        $app = Yii::$app;

        /** @var string[] $namespaces */
        $namespaces = is_string($app->settingsNamespaces) ? [$app->settingsNamespaces] : $app->settingsNamespaces;
        foreach ($namespaces as $namespace) {
            $class = $namespace . '\\' . Inflector::id2camel($blockName);
            /** @noinspection NotOptimalIfConditionsInspection */
            if (class_exists($class) && is_subclass_of($class, SettingsBlock::class)) {
                return $class;
            }
        }

        return false;
    }

    private static $_settingsBlocks = [];

    /**
     * @param $blockName
     * @return SettingsBlock
     */
    public static function getSettingsBlock($blockName)
    {
        if (!isset(self::$_settingsBlocks[$blockName])) {
            $class = self::getSettingsBlockClass($blockName);
            self::$_settingsBlocks[$blockName] = new $class;
        }
        return self::$_settingsBlocks[$blockName];
    }
}
