<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 01.09.2015
 * Time: 10:54
 */

use kartik\file\FileInputAsset;
use letyii\tinymce\Tinymce;
use yii\bootstrap\ActiveForm;
use yiicms\components\core\widgets\CloseButton;
use yiicms\components\core\widgets\LangDropdown;
use yiicms\components\core\widgets\SubmitButton;
use yiicms\models\content\Category;
use yiicms\models\content\Page;
use yiicms\modules\admin\controllers\PagesController;
use yiicms\modules\admin\models\pages\LoadImage;

/**
 * @var $this \yii\web\View
 * @var $model Page
 * @var $loadModel LoadImage
 * @var $categories Category[]
 */

FileInputAsset::register($this);

$webUpload = \Yii::getAlias('@webupload') . '/';

?>

<?php $form = ActiveForm::begin(['id' => PagesController::FORM_PAGE_EDIT]) ?>
<div class="row button-row">
    <div class="col-md-12 col-sm-12">
        <?= CloseButton::widget(); ?>
        <?= SubmitButton::widget([
            'value' => 'save-and-close',
            'style' => 'primary',
            'title' => Yii::t('yiicms', 'Сохранить и закрыть'),
            'align' => 'right',
        ]); ?>
        <?= SubmitButton::widget(['align' => 'right']); ?>
    </div>
</div>
<div class="row">
    <div class="col-md-9 col-sm-12">
        <?= $form->field($model, 'lang')->widget(LangDropdown::class); ?>
        <?= $form->field($model, 'title'); ?>
        <?= $form->field($model, 'slug'); ?>

        <?= $form->field($model, 'announce')->textarea(['rows' => 5]); ?>
        <?= $form->field($model, 'pageText')->widget(
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
        <div class="row">
            <div class="col-md-12 col-sm-12">
                <?= $this->render('_image-list', ['model' => $model]) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 col-sm-12">
                <?= $this->render('_load-images', ['loadModel' => $loadModel]) ?>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm12">
        <?= $this->render('_publication-settings', ['form' => $form, 'model' => $model]) ?>
        <?= $this->render('_categories', ['model' => $model, 'categories' => $categories]) ?>
    </div>
</div>

<?php ActiveForm::end(); ?>
