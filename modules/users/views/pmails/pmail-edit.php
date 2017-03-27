<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 02.02.2016
 * Time: 10:53
 */

use letyii\tinymce\Tinymce;
use yii\bootstrap\ActiveForm;
use yii\web\View;
use yiicms\components\core\widgets\CloseButton;
use yiicms\components\core\widgets\SubmitButton;
use yiicms\models\core\PmailsOutgoing;

/**
 * @var $this View
 * @var $model PmailsOutgoing
 */

?>

<?php $form = ActiveForm::begin(); ?>
<div class="row button-row">
    <div class="col-md-12 col-sm-12">
        <?= CloseButton::widget(); ?>
        <?= SubmitButton::widget(['value' => 'save']); ?>
        <?= SubmitButton::widget([
            'value' => 'save-and-close',
            'style' => 'primary',
            'title' => Yii::t('modules/users', 'Сохранить и закрыть'),
        ]); ?>
        <?= SubmitButton::widget([
            'icon' => 'send',
            'value' => 'send',
            'style' => 'warning',
            'title' => Yii::t('modules/users', 'Отправить'),
        ]); ?>
    </div>
</div>
<div class="row">
    <div class="col-md-12 col-sm-12">
        <?= $form->field($model, 'toUsersLogins')->textInput(['disabled' => 'disabled']); ?>
        <?= $form->field($model, 'subject')->textInput(); ?>
        <?= $form->field($model, 'msgText', ['enableClientValidation' => false])->widget(
            Tinymce::class,
            [
                'configs' => [
                    'language' => \Yii::$app->language,
                    'plugins' => 'pagebreak,fm,table,save,hr,image,link,emoticons,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,template,advlist,code',
                    'mode' => 'exact',
                    'height' => '450px',
                    'relative_urls' => false,
                ],
            ]
        ); ?>
    </div>
</div>
<?php ActiveForm::end(); ?>
