<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 13.01.2016
 * Time: 10:48
 */

namespace yiicms\components\core\blocks;

use yii\base\Widget;

/**
 * Class Block базовый класс для объектов содержимого блоков
 * @package yiicms\components\core
 * @property string $viewPath папка в которой содержится шаблон блока
 * @property string $viewFile файл в котром находится шаблон
 */
class BlockWidget extends Widget
{
    public $title = 'Неопределнный блок';

    // ---------------------------------- геттеры и сеттеры ---------------------------------------------------------
    private $_viewFile;

    /**
     * @return mixed
     */
    public function getViewFile()
    {
        if (empty($this->_viewFile)) {
            $this->setViewFile('index.php');
        }
        return $this->_viewFile;
    }

    /**
     * @param mixed $viewFile
     */
    public function setViewFile($viewFile)
    {
        $this->_viewFile = $this->viewPath . DIRECTORY_SEPARATOR . $viewFile;
    }

    private $_viewPath;

    public function getViewPath()
    {
        if ($this->_viewPath === null) {
            $refl = new \ReflectionClass($this);
            $this->_viewPath = pathinfo($refl->getFileName(), PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR . 'views';
        }
        return $this->_viewPath;
    }

    /**
     * @param string $viewPath
     */
    public function setViewPath($viewPath)
    {
        $this->_viewPath = $viewPath;
    }
}
