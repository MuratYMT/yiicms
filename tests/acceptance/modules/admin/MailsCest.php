<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 23.06.2016
 * Time: 16:03
 */

namespace tests\acceptance\modules\admin;

use tests\acceptance\AcceptanceCest;
use yiicms\models\core\Mails;
use yiicms\models\core\Users;
use yiicms\tests\_data\fixtures\models\core\MailsFixture;

class MailsCest extends AcceptanceCest
{
    public static function _cestFixtures()
    {
        return [
            MailsFixture::className(),
        ];
    }

    public function testMail(\AcceptanceTester $I)
    {
        $from = Users::findById(-1);
        $to = Users::findById(220);

        $I->assertNotFalse(Mails::send('passwordRestore', $from, $to, ['changeUrl' => 'link', 'user' => $to]));
        self::login($I);
        $I->amOnPage('/admin/mails');

        $I->see('Отправленные с сайта письма');
        $I->dontSee('404', 'header');
        $I->dontSee('403', 'header');

        $I->see('SuperUser', 'table>tbody>tr>td');
        $I->see('SXSXS', 'table>tbody>tr>td');
        $I->see('murat.yeskendirov@yandex.ru', 'table>tbody>tr>td');
        $I->see('Восстановление пароля на сайте', 'table>tbody>tr>td');

        $I->clickPopupMenu('span[class="glyphicon glyphicon-expand"]');
        $I->see('Чтобы начать процесс изменения пароля для пользователя');
    }
}
