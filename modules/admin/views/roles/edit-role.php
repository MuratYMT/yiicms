<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 11.03.2015
 * Time: 8:35
 * @var \yii\data\ActiveDataProvider $dataProvider Data provider
 */
use yii\bootstrap\ActiveForm;
use yii\widgets\Pjax;
use yiicms\components\core\widgets\Alert;
use yiicms\components\core\widgets\CancelButton;
use yiicms\components\core\widgets\SubmitButton;
use yiicms\modules\admin\models\roles\RoleEdit;

/**
 * @var $this \yii\web\View
 * @var $model RoleEdit
 */

?>

<div class="row">
    <div class="col-sm-12">
        <?php Pjax::begin() ?>
        <?php Alert::widget() ?>
        <?php $form = ActiveForm::begin(['layout' => 'horizontal', 'options' => ['data-pjax' => 1]]); ?>
        <div class="row">
            <div class="form-group col-md-12 col-sm-12">
                <?= $form->field($model, 'name')->textInput(); ?>
                <?= $form->field($model, 'description')->textInput(); ?>
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
