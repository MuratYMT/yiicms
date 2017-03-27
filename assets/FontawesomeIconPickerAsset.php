<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 21.02.2017
 * Time: 8:53
 */

namespace yiicms\assets;

use yii\web\AssetBundle;
use yii\web\JqueryAsset;

class FontawesomeIconPickerAsset extends AssetBundle
{
    public function __construct($config = [])
    {
        $this->sourcePath = '@vendor/bower/fontawesome-iconpicker/dist';
        $this->depends = [
            \rmrevin\yii\fontawesome\AssetBundle::className(),
            JqueryAsset::className()
        ];

        $this->js = [
            'js/fontawesome-iconpicker.js',
        ];

        $this->css = [
            'css/fontawesome-iconpicker.css',
        ];

        parent::__construct($config);
    }
}