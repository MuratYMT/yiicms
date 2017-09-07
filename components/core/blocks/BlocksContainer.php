<?php
/**
 * Created by PhpStorm.
 * User: murat
 * Date: 13.01.2016
 * Time: 20:17
 */

namespace yiicms\components\core\blocks;

use yiicms\components\YiiCms;
use yiicms\models\core\Blocks;
use yii\base\InvalidConfigException;
use yii\base\Widget;

/**
 * Class BlocksContainer
 * @package yiicms\components\core\blocks
 * @property string $position позиция на странице в которой выводятся блоки
 * @property string $layout макет используемый для помещения содержимого каждого блока
 */
class BlocksContainer extends Widget
{
    /**
     * @var string
     */
    private $_position;

    /**
     * @var bool|string
     */
    private $_layout = false;

    public function init()
    {
        parent::init();

        if ($this->position === null) {
            throw new InvalidConfigException('You must set property' . static::class . '::position before use');
        }
    }

    public function run()
    {
        $out = [];
        foreach (YiiCms::$app->blockService->forPosition($this->position, \Yii::$app->user->id) as $block) {
            /** @var BlockWidget $class */
            $class = $block->contentClass;
            $params = $block->params;
            $params['viewFile'] = $block->viewFile;
            $content = $class::widget($params);

            if ($this->layout !== null && $this->layout !== false) {
                $content = $this->render($this->layout, ['content' => $content]);
            }

            $out[] = $content;
        }

        return implode("\n", $out);
    }

    // -------------------------------------------- геттеры и сеттеры -------------------------------------------------------

    /**
     * @return string
     */
    public function getPosition()
    {
        return $this->_position;
    }

    /**
     * @param string $position
     */
    public function setPosition($position)
    {
        $this->_position = $position;
    }

    /**
     * @return boolean|string
     */
    public function getLayout()
    {
        return $this->_layout;
    }

    /**
     * @param boolean|string $layout
     */
    public function setLayout($layout)
    {
        $this->_layout = $layout;
    }
}
