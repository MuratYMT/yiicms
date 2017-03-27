<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 26.06.2015
 * Time: 16:06
 */

namespace yiicms\components\core\yii;

use yiicms\components\core\ArrayHelper;

class Theme extends \yii\base\Theme
{
    /**
     * @var string путь к публикуемой папке img
     */
    public $imgBasePath;

    /**
     * @var string url к публикуемой папки
     */
    public $imgBaseUrl;

    public function __construct($config = [])
    {
        $class = new \ReflectionClass($this);

        \Yii::setAlias('@theme', dirname($class->getFileName()));
        $this->basePath = '@theme';

        $this->pathMap = [
            '@app/views' => '@theme/views',
            '@app/modules' => '@theme/views/modules',
        ];

        parent::__construct($config);
    }

    public function init()
    {
        parent::init();
        list ($this->imgBasePath, $this->imgBaseUrl) = \Yii::$app->assetManager->publish('@theme/img');
    }

    /**
     * @return string Название темы
     */
    public static function themeTitle()
    {
        throw new \BadFunctionCallException('Method ' . static::class . '::themeTitle() not implementet');
    }

    /**
     * @return  string[] список доступных позиций в теме для размещения блоков
     */
    public static function positions()
    {
        return [];
    }

    /**
     * список классов тем доступных на сайте
     * @return string[]
     */
    public static function availableThemes()
    {
        /** @var CommonApplicationTrait $app */
        $app = \Yii::$app;

        $namespaces = ArrayHelper::asArray($app->themesNamespaces);
        $classses = [];
        foreach ($namespaces as $namespace) {
            self::scanRecursive($namespace, $classses);
        }
        return $classses;
    }

    private static function scanRecursive($namespace, &$themesClasses)
    {
        $path = \Yii::getAlias(str_replace('\\', '/', "@$namespace"));
        $files = scandir($path);
        foreach ($files as $file) {
            if ($file === '..' || $file === '.') {
                continue;
            }
            $f = $path . DIRECTORY_SEPARATOR . $file;
            if (is_file($f) && preg_match('/Theme\.php$/', $file)) {
                $class = $namespace . '\\' . pathinfo($f, PATHINFO_FILENAME);
                if (class_exists($class) && is_subclass_of($class, self::class)) {
                    $themesClasses[] = $class;
                }
            } elseif (is_dir($f)) {
                self::scanRecursive($namespace . '\\' . $file, $themesClasses);
            }
        }
    }
}
