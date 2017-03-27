<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 20.02.2017
 * Time: 15:05
 */

namespace yiicms\components\core\yii;

/**
 * Class CommonApplicationTrait
 * @package yiicms\components\core\yii
 */
trait CommonApplicationTrait
{
    /**
     * @var string
     * имя папки куда загружаются файлы пользователей
     */
    public $uploadFolder = 'upload';

    /** @var string|string[] Где хранятся настройки сайта доступные к изменению через web интерфейс */
    public $settingsNamespaces = 'yiicms\\settings';

    /** @var string|string[] Где хранятся блоки сайта */
    public $blocksNamespaces = 'yiicms\\blocks';

    /** @var string|string[] Где хранятся задания планировщика */
    public $cronjobsNamespaces = 'yiicms\\cronjobs';

    /** @var string|string[] Где хранятся темы */
    public $themesNamespaces = 'yiicms\\themes';

    /** @var string|string[] где хранятся файлы меню админки */
    public $adminMenuNamespaces = 'yiicms\\config\\adminmenu';

    /**
     * @inheritdoc
     * @throws \yii\base\InvalidParamException
     */
    protected function registerUloadFolder()
    {
        \Yii::setAlias('@uploadFolder', '@app/' . $this->uploadFolder);     ///путь на диске допапки загрузки
    }
}