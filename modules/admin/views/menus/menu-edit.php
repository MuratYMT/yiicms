<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 22.01.2016
 * Time: 12:35
 */

use yii\bootstrap\ActiveForm;
use yii\widgets\Pjax;
use yiicms\components\admin\ParentMenuDropDown;
use yiicms\components\core\widgets\Alert;
use yiicms\components\core\widgets\CancelButton;
use yiicms\components\core\widgets\FontawesomeIconPicker;
use yiicms\components\core\widgets\SubmitButton;
use yiicms\models\core\Menus;
use yiicms\models\core\MenusVisibleForPathInfo;

/**
 * @var $this \yii\web\View
 * @var $model Menus
 * @var $action string
 * @var $parentId integer
 * @var $menuId integer
 */

?>
<div class="row">
    <div class="col-sm-12">
        <?php Pjax::begin() ?>
        <?php Alert::widget() ?>
        <?php $form = ActiveForm::begin(['layout' => 'horizontal']); ?>
        <div class="row">
            <div class="form-group col-md-12 col-sm-12">
                <?= $form->field($model, 'parentId')->widget(ParentMenuDropDown::class); ?>
                <?= $model->renderMultilang($form, 'title'); ?>
                <?= $model->renderMultilang($form, 'subTitle'); ?>

                <?= $form->field($model, 'icon',
                    ['template' => "{label}\n<div class=\"input-group col-sm-6\" style=\"padding-left: 15px;padding-right: 15px;\">\n{input}<span class=\"input-group-addon\"></span>\n</div>\n{hint}\n{error}"])
                    ->widget(FontawesomeIconPicker::class); ?>
                <?= $form->field($model, 'link')->textInput(); ?>
                <?= $form->field($model, 'weight')->textInput(); ?>
                <?= $form->field($model, 'pathInfoVisibleOrder')->dropDownList(MenusVisibleForPathInfo::visibleOrderLabels()); ?>
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