<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 03.08.2016
 * Time: 14:48
 */

use yii\bootstrap\ActiveForm;
use yii\widgets\Pjax;
use yiicms\components\admin\SettingsGroup;
use yiicms\components\core\Url;
use yiicms\components\core\widgets\Alert;
use yiicms\components\core\widgets\SubmitButton;

/**
 * @var $this \yii\web\View
 * @var $models SettingsGroup[]
 * @var $settingsGroup string
 */
$request = \Yii::$app->request;
$currentUrl = $request->pathInfo;
?>
<?php Pjax::begin() ?>
<?= Alert::widget() ?>
<?php $form = ActiveForm::begin(['layout' => 'horizontal', 'options' => ['data-pjax' => 1]]); ?>
<div class="row button-row">
    <div class="col-md-12 col-sm-12">
        <?= SubmitButton::widget(); ?>
    </div>
</div>
<div class="row">
    <div class="col-md-12 col-sm-12">
        <div class="nav-tabs-custom">
            <ul id="myTab" class="nav nav-tabs" role="tablist">
                <?php foreach ($models as $model) : ?>
                    <li role="presentation" class="<?= $settingsGroup === $model->group ? 'active' : '' ?>">
                        <a href="<?= Url::toCurrent(['settingsGroup' => $model->group]) ?>"
                           aria-expanded="<?= $settingsGroup === $model->group ? 'true' : 'false' ?>">
                            <?= $model->groupTitle ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div id="myTabContent" class="tab-content">
                <div class="tab-pane active">
                    <?php foreach ($models as $model) : ?>
                        <?php if ($model->group === $settingsGroup) : ?>
                            <div class="row">
                                <div class="col-md-12 col-sm-12">
                                    <?php SettingsGroup::render($form, $model, $settingsGroup); ?>
                                </div>
                                <!-- /.col-md-9 -->
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>
<?php Pjax::end() ?>
