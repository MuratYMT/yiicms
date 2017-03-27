<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 15.01.2016
 * Time: 8:34
 */

use yiicms\components\core\Helper;
use yii\helpers\Html;
use yiicms\components\core\widgets\Menu;
use yiicms\models\core\Menus;

/**
 * @var $this \yii\web\View
 * @var $items array
 */

$z = 1;

?>
<?= Menu::widget([
    'items' => $items,
    'options' => [
        'class' => 'nav navbar-nav',
    ],
    'submenuTemplate' => "\n<ul class='dropdown-menu' role='menu' {show}>\n{items}\n</ul>\n",
    'linkTemplateParent' => '<a href="{url}" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">{icon} {label} <span class="caret"></span></a>',
]) ?>