<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 07.09.2015
 * Time: 8:05
 */

namespace yiicms\assets;

use yii\bootstrap\BootstrapPluginAsset;
use yii\jui\JuiAsset;
use yii\web\AssetBundle;
use yii\web\JqueryAsset;
use yii\widgets\PjaxAsset;

class CommonAsset extends AssetBundle
{
    public function __construct($config = [])
    {
        $this->sourcePath = __DIR__ . '/resource';
        $this->depends = [
           JqueryAsset::className(),
           BootstrapPluginAsset::className(),
           BootboxAsset::className(),
           JuiAsset::className(),
           PjaxAsset::className(),
       ];

       /*$thicss = [
           'common.css',
       ];
*/
       $this->js = [
           'yii.yiicms.js',
       ];

        parent::__construct($config);
    }
}
