<?php
/**
 * Created by PhpStorm.
 * User: murat
 * Date: 12.09.2015
 * Time: 21:23
 */

namespace yiicms\components\core\widgets;

use yii\base\Widget;

class Ajax extends Widget
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        echo '<div class="ajax-links">';
    }

    /**
     * Executes the widget.
     * @return string the result of widget execution to be outputted.
     */
    public function run()
    {
        return '</div>';
    }
}
