<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 25.01.2016
 * Time: 11:46
 */

use yii\bootstrap\ActiveForm;
use yii\widgets\Pjax;
use yiicms\components\core\widgets\Alert;
use yiicms\components\core\widgets\CancelButton;
use yiicms\components\core\widgets\SubmitButton;
use yiicms\models\core\MenusVisibleForPathInfo;

/**
 * @var $this \yii\web\View
 * @var $model MenusVisibleForPathInfo
 * @var $action string
 */

?>


<div class="row">
    <div class="col-sm-12">
        <?php Pjax::begin() ?>
        <?php Alert::widget() ?>
        <?php $form = ActiveForm::begin(['layout' => 'horizontal']); ?>
        <div class="row">
            <div class="form-group col-md-12 col-sm-12">
                <?= $form->field($model, 'rule')->dropDownList(MenusVisibleForPathInfo::ruleLabels()); ?>
                <?= $form->field($model, 'template')->textInput(); ?>
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
