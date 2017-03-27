<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 01.09.2015
 * Time: 11:50
 */

use yii\helpers\Html;
use yii\web\View;
use yiicms\models\content\Category;
use yiicms\models\content\CategoryPermission;
use yiicms\models\content\Page;

/**
 * @var $this View
 * @var $model Page
 * @var  $categories
 */
?>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><?= Yii::t('yiicms', 'Категории') ?></h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
        </div>
        <!-- /.box-tools -->
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <?php if (!empty($categories)) : ?>
            <?php foreach ($categories as $category) : ?>
                <div class="checkbox" style="margin-left: <?= ($category->levelNod - 1) * 35; ?>px">
                    <?= Html::checkbox(
                        Html::getInputName($model, 'categoriesIds') . '[]',
                        in_array($category->categoryId, $model->categoriesIds, false),
                        [
                            'value' => $category->categoryId,
                            'label' => $category->title,
                            'id' => Html::getInputId($model, 'categoriesIds') . $category->categoryId,
                        ]
                    ); ?>
                </div>
            <?php endforeach ?>
        <?php endif; ?>
    </div>
    <!-- /.box-body -->
</div>
