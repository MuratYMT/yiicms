<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 01.07.2015
 * Time: 15:05
 */
use yii\web\HttpException;
use yii\web\View;

use yii\helpers\Html;

/* @var $this View */
/* @var $name string */
/* @var $message string */
/* @var $exception HttpException */

if (empty($message)) {
    $message = $exception->getName();
}

?>

<div class="container body">
    <div class="main_container">
        <!-- page content -->
        <div class="col-md-12">
            <div class="col-middle">
                <div class="text-center text-center">
                    <h1 class="error-number"><?= Html::encode($exception->statusCode) ?></h1>
                    <h2><?= nl2br(Html::encode($message)) ?></h2>
                </div>
            </div>
        </div>
        <!-- /page content -->
    </div>
</div>
