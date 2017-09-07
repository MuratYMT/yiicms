<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 16.05.2016
 * Time: 12:55
 */

namespace common\unit\test\models\web;

use yiicms\components\core\DateTime;
use yiicms\components\YiiCms;
use yiicms\models\core\Mails;
use yiicms\models\core\Users;
use yiicms\tests\_data\fixtures\models\core\MailsFixture;
use tests\unit\UnitCest;
use yii\db\ActiveQuery;

class MailsCest extends UnitCest
{
    public static function _cestFixtures()
    {
        return [
            'mails' => MailsFixture::className(),
        ];
    }

    public function testRequireRule(\MyUnitTester $I)
    {
        $model = new Mails();
        $model->validate();
        $I->assertTrue($model->hasErrors('toLogin'));
        $I->assertTrue($model->hasErrors('email'));
        $I->assertTrue($model->hasErrors('subject'));
        $I->assertTrue($model->hasErrors('messageText'));
        $I->assertTrue($model->hasErrors('fromUserId'));
    }

    public function testRuleToLogin(\MyUnitTester $I)
    {
        $model = new Mails();
        $model->toLogin = '01234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789';
        $model->validate(['toLogin']);
        $I->assertFalse($model->hasErrors('toLogin'));
        $model->toLogin .= $model->toLogin;
        $model->validate(['toLogin']);
        $I->assertTrue($model->hasErrors('toLogin'));
    }

    public function testRuleToEMail(\MyUnitTester $I)
    {
        $model = new Mails();
        $model->email = 'zz';
        $model->validate(['email']);
        $I->assertTrue($model->hasErrors('email'));
        $model->email = 'murat_ymt@mail.ru';
        $model->validate(['email']);
        $I->assertFalse($model->hasErrors('email'));
    }

    public function testRuleFromUserId(\MyUnitTester $I)
    {
        $model = new Mails();
        $model->fromUserId = 'xxxxx';
        $model->validate(['fromUserId']);
        $I->assertTrue($model->hasErrors('fromUserId'));
        $model->fromUserId = -2;
        $model->validate(['fromUserId']);
        $I->assertTrue($model->hasErrors('fromUserId'));
        $model->fromUserId = -1;
        $model->validate(['fromUserId']);
        $I->assertFalse($model->hasErrors('email'));
    }

    public function testRuleSubject(\MyUnitTester $I)
    {
        $model = new Mails();

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
        $model = new Mails();
        $model->messageText = 'test text';
        $model->validate(['messageText']);
        $I->assertFalse($model->hasErrors('messageText'));

        $I->assertEquals('<p>test text</p>', $model->messageText);

        $model->messageText = '<script>var x="xdxd"</script>';
        $model->validate(['messageText']);
        $I->assertFalse($model->hasErrors('subject'));

        $I->assertEquals('', $model->messageText);
    }

    public function testSend(\MyUnitTester $I)
    {
        $fromUser = Users::findById(-1);
        $toUser = Users::findById(220);
        $mailService = YiiCms::$app->mailService;
        $result = $mailService->send('passwordRestore3', $fromUser, $toUser, ['user' => $toUser]);

        $I->assertFalse($result);

        $result = $mailService->send('passwordRestore', $fromUser, $toUser, ['user' => $toUser]);

        $I->assertNotFalse($result);

        /** @var Mails $mail */
        $mail = (new ActiveQuery(Mails::className()))
            ->select('*')
            ->from(Mails::tableName())
            ->limit(1)
            ->orderBy(['createdAt' => SORT_DESC])
            ->one();

        $I->assertNotNull($mail);
        $I->assertEquals($fromUser->userId, $mail->fromUserId);
        $I->assertEquals($toUser->email, $mail->email);
        $I->assertEquals(DateTime::runTime(), $mail->createdAt);
        $I->assertContains('Восстановление пароля на сайте', $mail->subject);
        $I->assertContains('Чтобы начать процесс изменения пароля для пользователя ' . $toUser->login, $mail->messageText);
    }

    public function testLinksFromUsers(\MyUnitTester $I)
    {
        $fromUser = Users::findById(-1);
        $toUser = Users::findById(220);

        $mails = YiiCms::$app->mailService->send('passwordRestore', $fromUser, $toUser, ['user' => $toUser]);

        $mailsFromUser = $mails->fromUser;

        $I->assertEquals($fromUser->userId, $mailsFromUser->userId);
        $I->assertEquals($fromUser->login, $mailsFromUser->login);
        $I->assertEquals($fromUser->login, $mails->fromLogin);
    }
}
