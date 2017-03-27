<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 15.06.2016
 * Time: 10:25
 */

namespace tests\acceptance\modules\users;

use tests\acceptance\AcceptanceCest;
use yiicms\models\core\PmailsOutgoing;
use yiicms\models\core\Users;
use yiicms\tests\_data\fixtures\models\core\PmailsFixtures;

class PMailCest extends AcceptanceCest
{
    public static function _cestFixtures()
    {
        return [
            PmailsFixtures::className(),
        ];
    }

    public function testIncomingDelete(\AcceptanceTester $I)
    {
        self::login($I);

        $fromUser = Users::findById(-1);
        $toUser = Users::findById(220);

        $incomingMail = PmailsOutgoing::sendTo($fromUser, $toUser, 'test subject', 'test message');

        $I->amOnPage('pmails');

        $I->see('Личные сообщения', 'h1');
        $I->see('Входящие', '.active');
        $I->see('Отправленные');
        $I->see('Черновики');

        $I->see($fromUser->login);
        $I->see($incomingMail->subject);

        $I->click('span.glyphicon-expand');

        $I->see('test message');

        $I->clickPopupMenu('Удалить', null, null, 1);

        $I->clickDlgConfirm();

        $I->dontSee(404, 'header');
        $I->dontSee($incomingMail->subject);
    }

    public function testIncomingReply(\AcceptanceTester $I)
    {
        self::login($I);

        $fromUser = Users::findById(-1);
        $toUser = Users::findById(220);

        $incomingMail = PmailsOutgoing::sendTo($fromUser, $toUser, 'test subject', 'test message');

        $I->amOnPage('pmails');

        $I->clickPopupMenu('Ответить');

        $I->dontSee(404, 'header');

        $I->seeInField('input[name="PmailsOutgoing[toUsersLogins]"]', $fromUser->login);
        $I->seeInField('input[name="PmailsOutgoing[subject]"]', 'Re: ' . $incomingMail->subject);

        $I->click('Отправить');

        $I->dontSee(404, 'header');

        $I->see('Отправленные', '.active');
        $I->see('Re: ' . $incomingMail->subject);
        $I->see($fromUser->login);
    }

    public function testIncomingForward(\AcceptanceTester $I)
    {
        self::login($I);

        $fromUser = Users::findById(-1);
        $toUser = Users::findById(220);

        $incomingMail = PmailsOutgoing::sendTo($fromUser, $toUser, 'test subject', 'test message');

        $I->amOnPage('pmails');

        $I->clickPopupMenu('Переслать');

        $I->dontSee(404, 'header');

        $I->see('Выберите получателя', 'h1');
        $I->click('SimpleUser');

        $I->seeInField('input[name="PmailsOutgoing[toUsersLogins]"]', 'SimpleUser');
        $I->seeInField('input[name="PmailsOutgoing[subject]"]', 'Fw: ' . $incomingMail->subject);

        $I->click('Отправить');

        $I->dontSee(404, 'header');

        $I->see('Отправленные', '.active');
        $I->see('Fw: ' . $incomingMail->subject);
        $I->see('SimpleUser');
    }

    public function testIncomingSetRead(\AcceptanceTester $I)
    {
        self::login($I);

        $fromUser = Users::findById(-1);
        $toUser = Users::findById(220);

        $incomingMail = PmailsOutgoing::sendTo($fromUser, $toUser, 'test subject', 'test message');

        $I->amOnPage('pmails');

        $I->see($incomingMail->subject);
        $I->see($incomingMail->subject, 'strong');

        $I->clickPopupMenu('Отметить прочтенным');

        $I->dontSee(404, 'header');

        $I->see('Сообщение отмечено как прочитанное', '.toast-message');
        $I->see($incomingMail->subject);
        $I->dontSee($incomingMail->subject, 'strong');

        $I->clickPopupMenu('Отметить не прочтенным');

        $I->dontSee(404, 'header');

        $I->see('Сообщение отмечено как не прочитанное', '.toast-message');
        $I->see($incomingMail->subject);
        $I->see($incomingMail->subject, 'strong');
    }

    public function testOutgoingDelete(\AcceptanceTester $I)
    {
        self::login($I);

        $fromUser = Users::findById(220);
        $toUser = Users::findById(-1);

        $sendedPMail = PmailsOutgoing::sendTo($fromUser, $toUser, 'test subject', 'test message');

        $I->amOnPage('pmails?activeTab=sended');

        $I->see('Личные сообщения', 'h1');
        $I->see('Входящие');
        $I->see('Отправленные', '.active');
        $I->see('Черновики');

        $I->see($toUser->login);
        $I->see($sendedPMail->subject);

        $I->click('span.glyphicon-expand');

        $I->see('test message');

        $I->clickPopupMenu('Удалить', null, null, 1);

        $I->clickDlgConfirm();

        $I->dontSee(404, 'header');
        $I->dontSee($sendedPMail->subject);
    }

    public function testOutgoingForward(\AcceptanceTester $I)
    {
        self::login($I);

        $fromUser = Users::findById(220);
        $toUser = Users::findById(-1);

        $incomingMail = PmailsOutgoing::sendTo($fromUser, $toUser, 'test subject', 'test message');

        $I->amOnPage('pmails?activeTab=sended');

        $I->clickPopupMenu('Переслать');

        $I->dontSee(404, 'header');

        $I->see('Выберите получателя', 'h1');
        $I->click('SimpleUser');

        $I->seeInField('input[name="PmailsOutgoing[toUsersLogins]"]', 'SimpleUser');
        $I->seeInField('input[name="PmailsOutgoing[subject]"]', 'Fw: ' . $incomingMail->subject);

        $I->click('Отправить');

        $I->dontSee(404, 'header');

        $I->see('Отправленные', '.active');
        $I->see('Fw: ' . $incomingMail->subject);
        $I->see('SimpleUser');
    }

    public function testDraft(\AcceptanceTester $I)
    {
        self::login($I);

        self::_createDraftMessage($I);

        $I->click('button[value="save"]');

        $I->dontSee(404, 'header');
        $I->see('Сообщение сохранено', '.toast-message');

        $I->seeInField('input[name="PmailsOutgoing[toUsersLogins]"]', 'SimpleUser');
        $I->seeInField('input[name="PmailsOutgoing[subject]"]', 'test message');

        $I->click('Сохранить и закрыть');

        $I->dontSee(404, 'header');
        $I->see('Сообщение сохранено', '.toast-message');

        $I->amOnPage('pmails?activeTab=draft');

        $I->dontSee(404, 'header');
        $I->see('Личные сообщения', 'h1');
        $I->see('Входящие');
        $I->see('Отправленные');
        $I->see('Черновики', '.active');

        $I->see('SimpleUser');
        $I->see('test message');

        $I->click('span.glyphicon-expand');

        $I->see('test message for user');
    }

    public function testEditDraft(\AcceptanceTester $I)
    {
        self::login($I);

        self::_createDraftMessage($I);

        $I->click('Сохранить и закрыть');

        $I->dontSee(404, 'header');
        $I->see('Сообщение сохранено', '.toast-message');

        $I->amOnPage('pmails?activeTab=draft');

        $I->clickPopupMenu('Редактировать');

        $I->dontSee(404, 'header');
        $I->seeInField('input[name="PmailsOutgoing[toUsersLogins]"]', 'SimpleUser');
        $I->seeInField('input[name="PmailsOutgoing[subject]"]', 'test message');

        $I->switchToIFrame('pmailsoutgoing-msgtext_ifr');
        $I->executeJS('document.getElementById("tinymce").innerHTML = "<p>test message for user2</p>";');
        $I->switchToIFrame();

        $I->click('Сохранить и закрыть');

        $I->dontSee(404, 'header');
        $I->see('Сообщение сохранено', '.toast-message');

        $I->see('SimpleUser');
        $I->see('test message');

        $I->click('span.glyphicon-expand');

        $I->see('test message for user2');
    }

    public function testSendDraftMessage(\AcceptanceTester $I)
    {
        self::login($I);

        self::_createDraftMessage($I);

        $I->click('Сохранить и закрыть');

        $I->dontSee(404, 'header');
        $I->see('Сообщение сохранено', '.toast-message');

        $I->amOnPage('pmails?activeTab=draft');

        $I->dontSee(404, 'header');

        $I->clickPopupMenu('Отправить', null, null, 1);

        $I->clickDlgConfirm();

        $I->dontSee(404, 'header');

        $I->see('Сообщение отправлено');
        $I->dontSee('SimpleUser');
        $I->dontSee('test message');
    }

    public function testSendNewMessage(\AcceptanceTester $I)
    {
        self::login($I);

        self::_createDraftMessage($I);

        $I->click('Отправить');

        $I->dontSee(404, 'header');
        $I->see('Сообщение отправлено', '.toast-message');

        $I->see('Отправленные', '.active');
        $I->see('SimpleUser');
        $I->see('test message');
    }

    /* public function testPopup(\AcceptanceTester $I)
     {
         \Yii::$app->cache->delete(PmailAlert::className());

         self::login($I);

         $fromUser = Users::findById(-1);
         $toUser = Users::findById(220);

         PmailsOutgoing::sendTo($fromUser, $toUser, 'test subject', 'test message');

         $I->amOnPage('/roles');
         $I->wait(1);
         $I->seeInPopupHeader('У вас есть непрочитанные личные сообщения');
         $I->see('test subject', 'li');

         Settings::set('users.pmails-alertBlockTimeout', 10);
         $I->amOnPage('/users');
         $I->wait(1);
         $I->dontSee('У вас есть непрочитанные личные сообщения');
         sleep(10);

         $I->amOnPage('/roles');
         $I->wait(1);
         $I->seeInPopupHeader('У вас есть непрочитанные личные сообщения');
         $I->see('test subject', 'li');

         PmailsOutgoing::sendTo($fromUser, $toUser, 'test subject2', 'test message');

         $I->amOnPage('/users');
         $I->wait(1);
         $I->seeInPopupHeader('У вас есть непрочитанные личные сообщения');
         $I->see('test subject', 'li');
         $I->see('test subject2', 'li');
     }*/

    // ------------------------------------------------------------------------------------------

    public static function _createDraftMessage(\AcceptanceTester $I)
    {
        $I->amOnPage('pmails');

        $I->click('Новое сообщение');

        $I->see('Выберите получателя', 'h1');
        $I->click('SimpleUser');

        $I->seeInField('input[name="PmailsOutgoing[toUsersLogins]"]', 'SimpleUser');
        $I->seeInField('input[name="PmailsOutgoing[subject]"]', '');

        $I->fillField('input[name="PmailsOutgoing[subject]"]', 'test message');
        $I->switchToIFrame('pmailsoutgoing-msgtext_ifr');
        $I->executeJS('document.getElementById("tinymce").innerHTML = "<p>test message for user</p>";');
        $I->switchToIFrame();
    }
}
