<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 07.09.2015
 * Time: 8:52
 */

namespace yiicms\assets;

use yii\bootstrap\BootstrapPluginAsset;
use yii\web\AssetBundle;
use yii\web\JqueryAsset;
use yii\web\YiiAsset;

class BootboxAsset extends AssetBundle
{
    public function __construct($config = [])
    {
        $this->sourcePath = '@vendor/bower/bootbox';
        $this->depends = [
            YiiAsset::className(),
            JqueryAsset::className(),
            BootstrapPluginAsset::className(),
        ];

        $this->js = [
            'bootbox.js',
        ];

        parent::__construct($config);

        $this->overrideSystemConfirm();
    }

    private function overrideSystemConfirm()
    {
        \Yii::$app->view->registerJs('
            yii.confirm = function(message, ok, cancel) {
                bootbox.confirm(message, function(result) {
                    if (result) { !ok || ok(); } else { !cancel || cancel(); }
                });
            }
        ');
    }
}
