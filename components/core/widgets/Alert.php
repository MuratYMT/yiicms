<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiicms\components\core\widgets;

use yii\bootstrap\Widget;
use yiicms\assets\AlertAsset;

/**
 * Alert widget renders a message from session flash. All flash messages are displayed
 * in the sequence they were assigned using setFlash. You can set message as following:
 * ```php
 * \Yii::$app->getSession()->setFlash('error', 'This is the message');
 * \Yii::$app->getSession()->setFlash('success', 'This is the message');
 * \Yii::$app->getSession()->setFlash('info', 'This is the message');
 * ```
 * Multiple messages could be set as follows:
 * ```php
 * \Yii::$app->getSession()->setFlash('error', ['Error 1', 'Error 2']);
 * ```
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @author Alexander Makarov <sam@rmcreative.ru>
 */
class Alert extends Widget
{
    /**
     * @var array the alert types configuration for the flash messages.
     * This array is setup as $key => $value, where:
     * - $key is the name of the session flash variable
     * - $value is the bootstrap alert type (i.e. danger, success, info, warning)
     */
    public $alertTypes = [
        'error',
        'success',
        'info',
        'warning',
    ];

    public $timeOut = 15000;
    public $extendedTimeOut = 3000;
    public $position = 'top-right';
    public $hideDuration = 2000;
    public $closeButton = true;
    public $newestOnTop = true;

    public function init()
    {
        parent::init();

        $session = \Yii::$app->getSession();
        $flashes = $session->getAllFlashes();

        foreach ($flashes as $type => $data) {
            if (in_array($type, $this->alertTypes, true)) {
                $data = (array)$data;

                $options = $this->options;
                $options['timeOut'] = $this->timeOut;
                $options['extendedTimeOut'] = $this->extendedTimeOut;
                $options['positionClass'] = 'toast-' . $this->position;
                $options['hideDuration'] = $this->hideDuration;
                $options['closeButton'] = $this->closeButton;
                $options['newestOnTop'] = $this->newestOnTop;

                $options = json_encode($options);

                foreach ($data as $i => $message) {
                    $message = htmlspecialchars($message);
                    $this->view->registerJs("toastr.$type('$message', '', $options)");
                }

                $session->removeFlash($type);
            }
        }

        AlertAsset::register($this->view);
    }

    /**
     * всплывающее окно с ошибкой
     * @param string $text
     */
    public static function error($text)
    {
        \Yii::$app->session->addFlash('error', $text);
    }

    public static function success($text)
    {
        \Yii::$app->session->addFlash('success', $text);
    }

    public static function info($text)
    {
        \Yii::$app->session->addFlash('info', $text);
    }

    public static function warning($text)
    {
        \Yii::$app->session->addFlash('warning', $text);
    }
}
