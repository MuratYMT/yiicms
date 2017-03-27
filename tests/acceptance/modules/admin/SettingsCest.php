<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 24.11.2016
 * Time: 15:44
 */

namespace tests\acceptance\modules\admin;

use tests\acceptance\AcceptanceCest;
use yiicms\tests\_data\fixtures\models\core\SettingsFixture;

class SettingsCest extends AcceptanceCest
{
    public static function _cestFixtures()
    {
        return [
            SettingsFixture::className(),
        ];
    }

    public function testIndex(\AcceptanceTester $I)
    {
        self::_openSettingsPage($I);
        $I->see('Быстродействие');
        $I->click('Быстродействие', '#myTab');

        $I->dontSee(404, 'header');
        $I->dontSee(403, 'header');
        $I->see('Настройки сайта', 'h1');
        $I->see('Загрузка файлов');
        $I->click('Загрузка файлов', '#myTab');

        $I->dontSee(404, 'header');
        $I->dontSee(403, 'header');
        $I->see('Настройки сайта', 'h1');
        $I->see('Основные');
        $I->click('Основные', '#myTab');

        $I->dontSee(404, 'header');
        $I->dontSee(403, 'header');
        $I->see('Настройки сайта', 'h1');
        $I->see('Пользователи');

        $I->dontSee(404, 'header');
        $I->dontSee(403, 'header');
        $I->see('Настройки сайта', 'h1');
        $I->click('Пользователи', '#myTab');

        $I->dontSee(404, 'header');
        $I->dontSee(403, 'header');
        $I->see('Настройки сайта', 'h1');
    }

    public function editSettings(\AcceptanceTester $I)
    {
        self::_openSettingsPage($I);
        $I->click('Быстродействие');

        $I->seeOptionIsSelected('select[name="SettingsGroup[uploadStructureDepth]"]', '4096');
        $I->seeInField('input[name="SettingsGroup[cacheDuration]"]', 3600);

        $I->fillField('input[name="SettingsGroup[cacheDuration]"]', 7200);

        $I->click('Сохранить');

        $I->see('Настройки сохранены', '.toast-message');

        $I->seeInField('input[name="SettingsGroup[cacheDuration]"]', 7200);
    }

    // -----------------------------------------------------------------------------------------------------------------------------------------------

    public static function _openSettingsPage(\AcceptanceTester $I, $auth = true)
    {
        if ($auth) {
            self::login($I);
        }
        $I->amOnPage('/admin/settings');

        $I->dontSee(404, 'header');
        $I->dontSee(403, 'header');
        $I->see('Настройки сайта', 'h1');
    }
}
