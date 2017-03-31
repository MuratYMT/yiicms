<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 27.02.2017
 * Time: 10:19
 */

namespace yiicms\modules\admin\components\adminlte;

use yii\helpers\Html;

class GridView extends \kartik\grid\GridView
{
    public function __construct(array $config = [])
    {
        $this->export = false;
        parent::__construct($config);
    }

    public function run()
    {
        Html::addCssClass($this->options, 'box');
        Html::addCssClass($this->containerOptions, 'box-body');
        Html::addCssClass($this->tableOptions, 'table-hover dataTable');
        parent::run();
    }
}