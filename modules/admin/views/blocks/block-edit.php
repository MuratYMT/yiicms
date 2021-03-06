<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 13.01.2016
 * Time: 10:27
 */

use yii\bootstrap\ActiveForm;
use yii\widgets\Pjax;
use yiicms\components\core\widgets\Alert;
use yiicms\components\core\widgets\CancelButton;
use yiicms\components\core\widgets\SubmitButton;

/**
 * @var $this \yii\web\View
 * @var $model \yiicms\components\core\blocks\BlockEditor
 * @var $action string
 */

?>
<div class="row">
    <div class="col-sm-12">
        <?php Pjax::begin() ?>
        <?php Alert::widget()?>
        <?php $form = ActiveForm::begin(['layout' => 'horizontal', 'options' => ['data-pjax' => 1]]); ?>
        <div class="row">
            <div class="form-group col-md-12 col-sm-12">
                <?php $model->renderField($form); ?>
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
