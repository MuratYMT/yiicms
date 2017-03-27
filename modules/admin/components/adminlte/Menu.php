<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 22.02.2017
 * Time: 8:32
 */

namespace yiicms\modules\admin\components\adminlte;

use yiicms\components\core\ArrayHelper;
use yiicms\components\core\yii\CommonApplicationTrait;

class Menu extends \yiicms\components\core\widgets\Menu
{
    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->options['id'] = 'side-menu';

        if (empty($this->items)) {
            /** @var CommonApplicationTrait $app */
            $app = \Yii::$app;
            $namespaces = ArrayHelper::asArray($app->adminMenuNamespaces);
            $menuFiles = [];
            foreach ($namespaces as $namespace){
                $path = \Yii::getAlias(str_replace('\\', '/', "@$namespace"));
                $files = scandir(\Yii::getAlias($path));
                foreach ($files as $file) {
                    if ($file === '..' || $file === '.') {
                        continue;
                    }
                    $f = $path . DIRECTORY_SEPARATOR . $file;
                    if (is_file($f)) {
                        $menuFiles[$file] = $f;
                    }
                }
            }

            //сортируем по названию файла
            ksort($menuFiles, SORT_NATURAL);
            $items = [];
            foreach ($menuFiles as $file) {
                $items = ArrayHelper::merge($items, require $file);
            }

            $this->items = $items;
        }

        parent::run();
    }
}
