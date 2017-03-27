<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 15.10.2015
 * Time: 14:35
 */
use yii\bootstrap\ActiveForm;
use yii\web\View;
use yii\widgets\Pjax;
use yiicms\components\core\widgets\Alert;
use yiicms\components\core\widgets\SubmitButton;

/**
 * @var $this View
 * @var $model \yiicms\models\content\Comment
 * @var $action string
 */

?>
<?php Pjax::begin(['id' => 'reply-placeholder-' . $model->parentId]) ?>
<?php Alert::widget() ?>
<?php $form = ActiveForm::begin([
    'id' => 'category-edit-form',
    'action' => $action,
    'class' => 'clearfix',
]); ?>
<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <?= $form->field($model, 'commentText', ['enableLabel' => false])->textarea(['placeholder' => 'Введите текст сообщения...']) ?>
        </div>
    </div>
</div>
<div class="form-group clearfix">
    <?= SubmitButton::widget(['title' => \Yii::t('modules/content', 'Комментировать'), 'icon' => 'floppy-o', 'style' => 'default']); ?>
</div>
<?php ActiveForm::end() ?>
<?php Pjax::end() ?>
