<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 14.03.2017
 * Time: 9:20
 */

namespace yiicms\assets;

use yii\web\AssetBundle;
use yii\web\JqueryAsset;

class AlertAsset extends AssetBundle
{
    public function __construct($config = [])
    {
        $this->sourcePath = __DIR__ . '/toastr';
        $this->depends = [JqueryAsset::className()];

        $this->js = [
            'toastr.js',
        ];

        $this->css = [
            'toastr.css',
        ];

        parent::__construct($config);
    }
}