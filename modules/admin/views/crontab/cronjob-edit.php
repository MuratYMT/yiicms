<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 01.02.2016
 * Time: 14:36
 */

use yii\bootstrap\ActiveForm;
use yii\widgets\Pjax;
use yiicms\components\core\widgets\Alert;
use yiicms\components\core\widgets\CancelButton;
use yiicms\components\core\widgets\SubmitButton;
use yii\web\View;
use yiicms\models\core\Crontabs;

/**
 * @var $this View
 * @var $model Crontabs
 */

?>

<div class="row">
    <div class="col-sm-12">
        <?php Pjax::begin() ?>
        <?php Alert::widget() ?>
        <?php $form = ActiveForm::begin(['layout' => 'horizontal', 'options' => ['data-pjax' => 1]]); ?>
        <div class="row">
            <div class="form-group col-md-12 col-sm-12">
                <?= $form->field($model, 'jobClass')->dropDownList(Crontabs::getJobClassDropDown()); ?>
                <?= $form->field($model, 'runTime')->textInput(); ?>
                <em>&lt;Минуты&gt;&lt;Часы&gt;&lt;Дни_месяца&gt;&lt;Месяцы&gt;&lt;Дни_недели&gt;, <br/>
                    */4 каждые 4 минуты или каждые 4 часа,
                    <br/>* каждый час или день</em>
            </div>
        </div>
        <div class="row button-row">
            <div class="col-sm-offset-3 col-sm-5">
                <?= CancelButton::widget(); ?>
                <?= SubmitButton::widget(); ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
        <?php Pjax::end() ?>
    </div>
</div>