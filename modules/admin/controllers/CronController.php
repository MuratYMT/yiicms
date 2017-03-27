<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 28.01.2016
 * Time: 15:23
 */

namespace yiicms\modules\admin\controllers;

use yiicms\models\core\Crontabs;
use yii\console\Controller;

class CronController extends Controller
{
    public function actionIndex()
    {
        Crontabs::startAll();
    }
}