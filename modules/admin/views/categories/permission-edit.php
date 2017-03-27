<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 28.08.2015
 * Time: 11:17
 */

use yii\bootstrap\ActiveForm;
use yii\widgets\Pjax;
use yiicms\components\core\widgets\Alert;
use yiicms\components\core\widgets\CancelButton;
use yiicms\components\core\widgets\SubmitButton;
use yiicms\models\content\CategoryPermission;
use yiicms\modules\admin\models\categories\CategoryPermissionEditForm;

/**
 * @var $this \yii\web\View
 * @var $model CategoryPermissionEditForm
 * @var $categoryId integer
 * @var $roleName string
 */

?>
<div class="row">
    <div class="col-sm-12">
        <?php Pjax::begin() ?>
        <?php Alert::widget() ?>
        <?php $form = ActiveForm::begin(['layout' => 'horizontal', 'options' => ['data-pjax' => 1]]); ?>
        <div class="row">
            <div class="form-group col-md-12 col-sm-12">
                <?php foreach (CategoryPermission::$permissions as $perm) : ?>
                    <?= $form->field($model, $perm)->checkbox() ?>
                <?php endforeach; ?>
                <div class="form-group clearfix">
                    <?= $form->field($model, 'recursive')->checkbox() ?>
                </div>
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