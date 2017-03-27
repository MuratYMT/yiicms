<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 17.03.2017
 * Time: 8:12
 */
use kartik\widgets\DateTimePicker;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use yii\web\View;
use yiicms\components\core\widgets\AutoComplete;
use yiicms\models\content\Page;
use yiicms\components\core\DateTime;

/**
 * @var $this View
 * @var $model Page
 * @var $form ActiveForm
 */

?>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><?= Yii::t('yiicms', 'Параметры публикации') ?></h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
            </button>
        </div>
        <!-- /.box-tools -->
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <?= $form->field($model, 'createdAt')->textInput(['disabled' => true]); ?>
        <?= $form->field($model, 'publishedAt')->widget(DateTimePicker::class, [
            'convertFormat' => true,
            'pluginOptions' => [
                'autoclose' => true,
                'format' => DateTime::DATETIME_FORMAT,
                'language' => \Yii::$app->language,
                'todayHighlight' => true,
            ],
        ]); ?>

        <?= $form->field($model, 'startPublicationDate')->widget(DateTimePicker::class, [
            'convertFormat' => true,
            'pluginOptions' => [
                'autoclose' => true,
                'format' => DateTime::DATETIME_FORMAT,
                'language' => \Yii::$app->language,
                'todayHighlight' => true,
            ],
        ]); ?>
        <?= $form->field($model, 'endPublicationDate')->widget(DateTimePicker::class, [
            'convertFormat' => true,
            'pluginOptions' => [
                'autoclose' => true,
                'format' => DateTime::DATETIME_FORMAT,
                'language' => \Yii::$app->language,
                'todayHighlight' => true,
            ],
        ]); ?>
        <?= $form->field($model, 'toFirst')->checkbox(); ?>
        <?= $form->field($model, 'published')->checkbox(); ?>
        <?= $form->field($model, 'opened')->checkbox(); ?>
        <?= $form->field($model, 'keywords')->textarea(['rows' => 5]); ?>
        <?= $form->field($model, 'tagsString')->widget(AutoComplete::class, [
            'clientOptions' => [
                'minLength' => 0,
                'autoFill' => true,
                'source' => new \yii\web\JsExpression(
                    'function (request, response){
                        yii.yiicms.loadTag("' . Url::to(['/content/tags/search?tag=']) . '", request.term, response);
                     }'
                ),
            ],
            'useTextArea' => true,
            'options' => ['rows' => 5],
        ]); ?>
    </div>
    <!-- /.box-body -->
</div>