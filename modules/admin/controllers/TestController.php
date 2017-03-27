<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 01.04.2016
 * Time: 8:03
 */

namespace yiicms\modules\admin\controllers;

use yii\web\Controller;


class TestController extends Controller
{
    public function actionPass()
    {
        echo \Yii::$app->security->generatePasswordHash('SuperUser')."\n";
        echo \Yii::$app->security->generatePasswordHash('AnotherUser')."\n";
        echo \Yii::$app->security->generatePasswordHash('SXSXS')."\n";
        echo \Yii::$app->security->generatePasswordHash('SimpleUser')."\n";
        echo \Yii::$app->security->generatePasswordHash('not_active')."\n";
    }
}
