<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 16.06.2016
 * Time: 11:22
 */

namespace tests\acceptance\modules\users;

use yiicms\models\core\Users;
use tests\acceptance\AcceptanceCest;
use yiicms\tests\_data\fixtures\models\core\UsersFixture;

class UserCest extends AcceptanceCest
{
    public static function _cestFixtures()
    {
        return [
            UsersFixture::className(),
        ];
    }

    public function testLogin(\AcceptanceTester $I)
    {
        $I->amOnPage('login');

        $I->see('Вход', '.login-box-msg');
        $I->seeElement('input[placeholder="E-mail"]');
        $I->seeElement('input[placeholder="Пароль"]');
        $I->see('Запомнить меня');
        $I->dontSee('403', 'header');
        $I->dontSee('404', 'header');
        $I->dontSee('500', 'header');

        $I->click('Забыли пароль?');

        $I->see('Сброс пароля', '.login-box-msg');
        $I->amOnPage('login');

        $I->click('Регистрация');

        $I->see('Регистрация', '.register-box-msg');

        //неверный пароль
        $I->amOnPage('login');

        $I->fillField('#loginform-email', 'SXSXS');
        $I->fillField('#loginform-password', 'SXSXS2');
        $I->click('form button[type=submit]');

        $I->see('Неверный пароль или профиль');

        $I->fillField('#loginform-email', 'SXSXS2');
        $I->fillField('#loginform-password', 'SXSXS');
        $I->click('form button[type=submit]');

        $I->see('Неверный пароль или профиль');

        $I->fillField('#loginform-email', 'SXSXS');
        $I->fillField('#loginform-password', 'SXSXS');
        $I->click('form button[type=submit]');

        $I->see('Вы успешно вошли');
    }

    /*public function testAjaxLogin(\AcceptanceTester $I)
    {
        $I->amOnPage('/');
        $I->click('Вход');
        $I->seeInPopupHeader('Вход');

        //неверный пароль

        $I->fillField('#loginform-email', 'SXSXS');
        $I->fillField('#loginform-password', 'SXSXS2');
        $I->click('form button[type=submit]');

        $I->see('Неверный пароль или профиль');

        $I->fillField('#loginform-email', 'SXSXS2');
        $I->fillField('#loginform-password', 'SXSXS');
        $I->click('form button[type=submit]');

        $I->see('Неверный пароль или профиль');

        $I->fillField('#loginform-email', 'SXSXS');
        $I->fillField('#loginform-password', 'SXSXS');
        $I->click('form button[type=submit]');

        $I->see('Вы успешно вошли');
    }*/

    public function testPasswordReset(\AcceptanceTester $I)
    {
        $I->amOnPage('reset-password');

        $I->see('Сброс пароля', '.login-box-msg');
        $I->fillField('#resetpasswordform-email', 'ZZZZZ');
        $I->click('form button[type=submit]');

        $I->see('Неизвестный пользователь', '.help-block-error');

        $I->fillField('#resetpasswordform-email', 'SXSXS');
        $I->see('Неизвестный пользователь', '.help-block-error');
        $I->click('form button[type=submit]');

        $I->fillField('#resetpasswordform-email', 'murat_ymt@mail.ru');
        $I->click('form button[type=submit]');

        $I->see('На адрес murat_ymt@mail.ru было отправлено письмо с инструкцией для восстановления пароля', '.toast-message');

        //смена пароля
        //неверный токен
        $I->amOnPage('restore-password?token=rytrytrytry');

        $I->see('Восстановление пароля', '.login-box-msg');
        $I->fillField('#restorepasswordform-password', 'test#123');
        $I->fillField('#restorepasswordform-password2', 'test#123');
        $I->click('form button[type=submit]');

        $I->see('Неверный токен. Запросите сброс пароля еще раз', '.help-block-error');

        $token = Users::findOne(220)->token;

        //все правильно
        $I->amOnPage('restore-password?token=' . $token);

        $I->see('Восстановление пароля', '.login-box-msg');
        $I->fillField('#restorepasswordform-password', 'test#123');
        $I->fillField('#restorepasswordform-password2', 'test#123');
        $I->click('form button[type=submit]');

        $I->see('Пароль успешно изменен. Теперь вы можете войти', '.toast-message');

        //повторный сброс с использованным токеном
        $I->amOnPage('restore-password?token=' . $token);
        $I->fillField('#restorepasswordform-password', 'test#123');
        $I->fillField('#restorepasswordform-password2', 'test#123');
        $I->click('form button[type=submit]');

        $I->see('Неверный токен. Запросите сброс пароля еще раз', '.help-block-error');

        //повторный сброс проверка ссылки
        $I->amOnPage('restore-password?token=' . $token);
        $I->seeLink('Повторный сброс пароля');
        $I->click('Повторный сброс пароля');

        $I->see('Сброс пароля', '.login-box-msg');

        $I->amOnPage('login');
        $I->fillField('#loginform-email', 'SXSXS');
        $I->fillField('#loginform-password', 'test#123');
        $I->click('form button[type=submit]');

        $I->see('Вы успешно вошли');
    }

    public function testRegistration(\AcceptanceTester $I)
    {
        $I->amOnPage('registration');

        $I->see('Регистрация', '.register-box-msg');
        $I->seeLink('Уже зарегестрированы?');

        $I->fillField('#registrationform-email', 'm@mail.ru');
        $I->fillField('#registrationform-login', 'SXSXS2');
        $I->fillField('#registrationform-password', 'test#123');
        $I->fillField('#registrationform-password2', 'test#123');
        $I->selectOption('#registrationform-timezone', 'Asia/Almaty');
        $I->fillField('#registrationform-verifycode', 'testme');
        $I->uncheckOption('#registrationform-ruleread');
        $I->click('Зарегестрироваться');

        $I->see('Вы должны согласится с правилами', '.toast-message');

        $I->checkOption('#registrationform-ruleread');
        $I->click('Зарегестрироваться');

        $I->see('Регистрация завершилась успешно. Теперь вы можете войти', '.toast-message');
    }
}
