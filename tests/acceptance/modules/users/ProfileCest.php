<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 16.06.2016
 * Time: 11:21
 */

namespace tests\acceptance\modules\users;

use tests\acceptance\AcceptanceCest;
use yiicms\tests\_data\fixtures\models\core\UsersFixture;

class ProfileCest extends AcceptanceCest
{
    public static function _cestFixtures()
    {
        return [
            UsersFixture::className(),
        ];
    }

    public function testChangePassword(\AcceptanceTester $I)
    {
        self::login($I);

        $I->amOnPage('profile/change-password');

        $I->see('Смена пароля', 'h1');
        $I->see('Новый пароль');
        $I->see('Подтверждение');

        $I->fillField('#changepasswordform-oldpassword', 'zzz1234');
        $I->fillField('#changepasswordform-password', 'zzz123466');
        $I->fillField('#changepasswordform-password2', 'zzz123466');
        $I->click('Сменить пароль');

        $I->see('Неверный старый пароль');

        $I->fillField('#changepasswordform-oldpassword', 'SXSXS');
        $I->fillField('#changepasswordform-password', 'zzz123466');
        $I->fillField('#changepasswordform-password2', 'zzz123466');
        $I->click('Сменить пароль');

        $I->see('Пароль изменен', '.toast-message');
    }

    public function testProfile(\AcceptanceTester $I)
    {
        self::login($I);

        $I->amOnPage('profile');

        $I->see('Профиль', 'h1');
        $I->seeInField('#users-login', 'SXSXS');
        $I->seeInField('#users-email', 'murat.yeskendirov@yandex.ru');

        $I->fillField('#users-fio', 'Admin 123');
        $I->fillField('#users-icq', '123456');
        $I->fillField('#users-facebook', 'zzz.xyz');
        $I->click('Сохранить');

        $I->see('Данные профиля обновлены', '.toast-message');
        $I->seeInField('#users-login', 'SXSXS');
        $I->seeInField('#users-fio', 'Admin 123');
        $I->seeInField('#users-icq', '123456');
        $I->seeInField('#users-facebook', 'zzz.xyz');
    }
}
