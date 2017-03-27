<?php

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 * @SuppressWarnings(PHPMD)
 */
class AcceptanceTester extends \Codeception\Actor
{
    const TIME_OUT = 3;
    use _generated\AcceptanceTesterActions {
        _generated\AcceptanceTesterActions::wait as waitTait;
        _generated\AcceptanceTesterActions::click as clickTait;
        _generated\AcceptanceTesterActions::pressKey as pressKeyTait;
        _generated\AcceptanceTesterActions::amOnPage as amOnPageTait;
    }

    public function assertNotFalse($condition, $message = '')
    {
        return $this->assertNotSame(false, $condition, $message);
    }

    /**
     * нажатие всплывающего меню
     * @param string $link
     * @param string $tableId
     * @param string $context
     * @param int $timeout
     */
    public function clickPopupMenu($link, $tableId = null, $context = null, $timeout = self::TIME_OUT)
    {
        $this->click(($tableId === null ? '' : '#' . $tableId . ' ') . 'button[data-toggle=dropdown]', $context, 1);
        $this->click($link, ($tableId === null ? '' : '#' . $tableId . ' ') . $context, $timeout);
    }

    /**
     * пауза
     * @param int $timeout
     */
    public function wait($timeout = self::TIME_OUT)
    {
        $this->waitTait($timeout);
        //$this->waitForJS("return $.active == 0;", 60);
    }

    /**
     * вызов всплывающего меню
     */
    public function clickDlgConfirm()
    {
        $this->click('.modal-dialog button[data-bb-handler="confirm"]');
    }

    /**
     * вызов всплывающего меню
     */
    public function clickDlgOk()
    {
        $this->click('.bootbox-alert button[data-bb-handler="ok"]');
    }

    /**
     * клик с задержкой
     * @param string $link
     * @param string $context
     * @param int $timeout
     */
    public function click($link, $context = null, $timeout = self::TIME_OUT)
    {
        $this->clickTait($link, $context);
        $this->wait($timeout);
    }

    /**
     * нажатие кнопки с задержкой
     * @param $element
     * @param $char
     * @param int $timeout
     */
    public function pressKey($element, $char, $timeout = self::TIME_OUT)
    {
        $this->pressKeyTait($element, $char);
        $this->wait($timeout);
    }

    public function seeInPopupHeader($title)
    {
        return $this->see($title, 'h3[class="modal-title"]');
    }

    public function amOnPage($page)
    {
        //$page .= (strpos($page, '?') !== false ? '&' : '?') . 'XDEBUG_SESSION_START=14756';
        return $this->amOnPageTait($page);
    }
}
