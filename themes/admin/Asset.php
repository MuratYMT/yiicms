<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 06.03.2017
 * Time: 10:53
 */

namespace yiicms\themes\admin;

use yii\base\Exception;
use yii\bootstrap\BootstrapAsset;
use yii\bootstrap\BootstrapPluginAsset;
use yii\web\AssetBundle;
use yii\web\YiiAsset;
use yiicms\assets\CommonAsset;

class Asset extends AssetBundle
{
    public function __construct(array $config = [])
    {
        $this->sourcePath = __DIR__ . '/source/dist';
        $this->css = [
            'css/AdminLTE.css',
        ];
        $this->js = [
            'js/app.js',
        ];

        $this->depends = [
            YiiAsset::className(),
            BootstrapAsset::className(),
            BootstrapPluginAsset::className(),
            \rmrevin\yii\fontawesome\AssetBundle::className(),
            CommonAsset::className(),
        ];
        parent::__construct($config);
    }

    /**
     * @var string|bool Choose skin color, eg. `'skin-blue'` or set `false` to disable skin loading
     * @see https://almsaeedstudio.com/themes/AdminLTE/documentation/index.html#layout
     */
    public $skin = 'skin-blue';

    /**
     * @inheritdoc
     */
    public function init()
    {
        // Append skin color file if specified
        if ($this->skin) {
            if (('_all-skins' !== $this->skin) && (strpos($this->skin, 'skin-') !== 0)) {
                throw new Exception('Invalid skin specified');
            }

            $this->css[] = sprintf('css/%s.css', $this->skin);
        }

        parent::init();
    }
}