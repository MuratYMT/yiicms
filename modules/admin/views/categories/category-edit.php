<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 18.08.2015
 * Time: 14:30
 */

use yii\bootstrap\ActiveForm;
use yii\widgets\Pjax;
use yiicms\components\content\CategoriesParentsWidget;
use yiicms\components\core\widgets\Alert;
use yiicms\components\core\widgets\CancelButton;
use yiicms\components\core\widgets\SubmitButton;
use yiicms\modules\admin\models\categories\CategoryEditForm;

/**
 * @var $this \yii\web\View
 * @var $model CategoryEditForm
 * @var $action string
 */

?>

<div class="row">
    <div class="col-sm-12">
        <?php Pjax::begin() ?>
        <?php Alert::widget() ?>
        <?php $form = ActiveForm::begin(['layout' => 'horizontal', 'options' => ['data-pjax' => 1]]); ?>
        <div class="row">
            <div class="form-group col-md-12 col-sm-12">
                <?= $form->field($model, 'parentId')->widget(CategoriesParentsWidget::class); ?>
                <?= $model->renderMultilang($form, 'title'); ?>
                <?= $form->field($model, 'description')->textInput() ?>
                <?= $form->field($model, 'slug')->textInput() ?>
                <?= $form->field($model, 'weight')->textInput() ?>
                <?= $form->field($model, 'keywords')->textarea() ?>
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