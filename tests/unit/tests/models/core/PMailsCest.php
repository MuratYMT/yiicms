<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 17.05.2016
 * Time: 8:08
 */

namespace common\unit\test\models\web;

use yiicms\components\core\DateTime;
use yiicms\components\YiiCms;
use yiicms\models\core\PmailsIncoming;
use yiicms\models\core\PmailsOutgoing;
use yiicms\models\core\PmailsUserStat;
use yiicms\models\core\Users;
use yiicms\tests\_data\fixtures\models\core\PmailsFixtures;
use tests\unit\UnitCest;

class PmailsCest extends UnitCest
{
    public static function _cestFixtures()
    {
        return [
            PmailsFixtures::className()
        ];
    }

    public function testRuleUsers(\MyUnitTester $I)
    {
        $pmail = new PmailsOutgoing();
        $pmail->scenario = PmailsOutgoing::SC_EDIT;
        $pmail->fromUserId = -2;
        $pmail->validate(['fromUserId']);
        $I->assertTrue($pmail->hasErrors('fromUserId'));

        $pmail->fromUserId = -1;
        $pmail->validate(['fromUserId']);
        $I->assertFalse($pmail->hasErrors('fromUserId'));

        $pmail->toUsers = new Users(['userId' => -2, 'login' => 'TestR1']);
        $pmail->validate(['toUsersList']);
        $I->assertTrue($pmail->hasErrors('toUsersList'));

        $pmail->toUsers = Users::findOne(['userId' => 220]);
        $pmail->validate(['toUsers']);
        $I->assertFalse($pmail->hasErrors('toUsers'));
    }

    public function testRuleSubject(\MyUnitTester $I)
    {
        $model = new PmailsOutgoing();

        $model->subject = '01234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789';
        $model->validate(['subject']);
        $I->assertFalse($model->hasErrors('subject'));

        $model->subject .= $model->subject;
        $model->subject .= $model->subject;
        $model->subject .= $model->subject;
        $model->validate(['subject']);
        $I->assertTrue($model->hasErrors('subject'));

        $model->subject = '<a>test text</a>';
        $model->validate(['subject']);
        $I->assertEquals('&lt;a&gt;test text&lt;/a&gt;', $model->subject);
    }

    public function testRuleMessageText(\MyUnitTester $I)
    {
        $model = new PmailsOutgoing();
        $model->msgText = 'test text one';
        $model->validate(['msgText']);
        $I->assertFalse($model->hasErrors('msgText'));

        $I->assertEquals('<p>test text one</p>', $model->msgText);

        $model->msgText = '<script>var x="xdxd"</script>';
        $model->validate(['msgText']);
        $I->assertFalse($model->hasErrors('subject'));

        $I->assertEquals('', $model->msgText);
    }

    public function testSend(\MyUnitTester $I)
    {
        $fromUser = Users::findById(-1);
        $toUser = Users::findById(220);

        $sendedPmail = PmailsOutgoing::sendTo($fromUser, $toUser, 'test subject', 'test message');

        $I->assertNotFalse($sendedPmail);

        /** @var PmailsOutgoing $pmail */
        $pmail = PmailsOutgoing::findOne(['rowId' => $sendedPmail->rowId]);

        $I->assertEquals($sendedPmail->toUsersList, $pmail->toUsersList);
        $I->assertEquals($sendedPmail->talkId, $pmail->talkId);
        $I->assertEquals($sendedPmail->fromUserId, $pmail->fromUserId);
        $I->assertEquals($sendedPmail->fromUserLogin, $pmail->fromUserLogin);
        $I->assertEquals('test subject', $pmail->subject);
        $I->assertEquals('<p>test message</p>', $pmail->msgText);
        $I->assertEquals(DateTime::runTime(), $pmail->sentAt);
        $I->assertEquals(1, $pmail->sended);
    }

    public function testMarkRead(\MyUnitTester $I)
    {
        $fromUser = Users::findById(-1);
        $toUser = Users::findById(220);

        /** @var PmailsOutgoing $sendedPMail */
        $sendedPMail = PmailsOutgoing::sendTo($fromUser, $toUser, 'test subject', 'test message');

        /** @var PmailsIncoming $pmail */
        $pmail = PmailsIncoming::findOne(['talkId' => $sendedPMail->talkId]);

        $result = $pmail->markRead();
        $I->assertNotFalse($result);

        /** @var PmailsIncoming $pmail */
        $pmail = PmailsIncoming::findOne(['talkId' => $sendedPMail->talkId]);

        $I->assertEquals(1, $pmail->readed);

        $result = $pmail->markUnRead();
        $I->assertNotFalse($result);

        $pmail = PmailsIncoming::findOne(['talkId' => $sendedPMail->talkId]);

        $I->assertEquals(0, $pmail->readed);
    }

    public function testLink(\MyUnitTester $I)
    {
        $fromUser = Users::findById(-1);
        $toUser = Users::findById(220);

        $sendedPMail = PmailsOutgoing::sendTo($fromUser, $toUser, 'test subject', 'test message');

        $user = $sendedPMail->fromUser;
        $I->assertEquals($fromUser->userId, $user->userId);

        $toUsers = $sendedPMail->toUsers;
        $user = array_shift($toUsers);
        $I->assertEquals($toUser->userId, $user->userId);
    }

    public function testCountOnUser(\MyUnitTester $I)
    {
        $pmailService = YiiCms::$app->pmailService;

        $fromUser = Users::findById(-1);
        $toUser = Users::findById(220);

        $pmail = PmailsOutgoing::sendTo($fromUser, $toUser, 'test subject', 'test message');

        $toUser = PmailsUserStat::findOne(220);
        $I->assertEquals(1, $toUser->notReadCount);
        $I->assertEquals(1, $toUser->totalCount);

        $fromUser = PmailsUserStat::findOne(-1);
        $I->assertEquals(0, $fromUser->notReadCount);
        $I->assertEquals(1, $fromUser->totalCount);

        $pmailIncoming = PmailsIncoming::findOne(['talkId' => $pmail->talkId]);

        $pmailIncoming->markRead();

        $toUser = PmailsUserStat::findOne(220);
        $I->assertEquals(0, $toUser->notReadCount);
        $I->assertEquals(1, $toUser->totalCount);

        $pmailIncoming->markUnRead();

        $toUser = PmailsUserStat::findOne(220);
        $I->assertEquals(1, $toUser->notReadCount);
        $I->assertEquals(1, $toUser->totalCount);

        $pmailService->incomingPmailDelete($pmailIncoming);

        $toUser = PmailsUserStat::findOne(220);
        $I->assertEquals(0, $toUser->notReadCount);
        $I->assertEquals(0, $toUser->totalCount);

        $pmailService->outgoingPmailDelete($pmail);

        $fromUser = PmailsUserStat::findOne(-1);
        $I->assertEquals(0, $fromUser->notReadCount);
        $I->assertEquals(0, $fromUser->totalCount);
    }
}
