<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 24.11.2016
 * Time: 11:36
 */

namespace tests\acceptance\modules\filemanager;

use tests\acceptance\AcceptanceCest;
use yiicms\tests\_data\fixtures\models\core\VFilesFixture;
use yiicms\tests\_data\fixtures\models\core\VFoldersFixture;

class FilemanagetCest extends AcceptanceCest
{
    public static function _cestFixtures()
    {
        return [
            VFilesFixture::className(),
            VFoldersFixture::className(),
        ];
    }

    public function testIndex(\AcceptanceTester $I)
    {
        self::_openManagersPage($I);
        $I->see('folder1', 'button');
        $I->see('folder11');
        $I->see('folder12');
        $I->see('folder13');

        $I->click('.icon-list a[href="/ru/filemanager?folderId=300"]');
        $I->see('folder12', 'button');
        $I->see('folder121');
        $I->see('folder122');
        $I->see('folder123');

        $I->click('.icon-list a[href="/ru/filemanager?folderId=500"]');
        $I->see('folder122', 'button');
        $I->see('folder1221');

        $I->clickPopupMenu('folder1');
        $I->see('folder11');
        $I->see('folder12');
        $I->see('folder13');
    }

    public function createFolder(\AcceptanceTester $I)
    {
        self::_openManagersPage($I);

        $I->click('Создать каталог');
        $I->see('Создать каталог', 'h1');
        $I->seeInField('input[name="VFolders[title]"]', '');

        $I->fillField('input[name="VFolders[title]"]', 'Тестовый каталог');

        $I->click('Сохранить');
        $I->see('Каталог "Тестовый каталог" создан', '.toast-message');
        self::_openManagersPage($I, false);
        $I->see('Тестовый каталог');
    }

    public function renameFolder(\AcceptanceTester $I)
    {
        self::_openManagersPage($I);
        $I->moveMouseOver('.icon-list a[href="/ru/filemanager?folderId=200"]');
        $I->click('.manage-box a[href*="rename-folder"]');
        $I->see('Переименовать каталог "folder11"', 'h1');
        $I->seeInField('input[name="VFolders[title]"]', 'folder11');

        $I->fillField('input[name="VFolders[title]"]', 'Тестовый каталог22');

        $I->click('Сохранить');
        $I->see('Каталог "Тестовый каталог22" переименован', '.toast-message');
        self::_openManagersPage($I, false);
        $I->see('Тестовый каталог22');
    }

    public function deleteFolder(\AcceptanceTester $I)
    {
        self::_openManagersPage($I);
        $I->see('folder11');
        $I->moveMouseOver('.icon-list a[href="/ru/filemanager?folderId=200"]');
        $I->click('.manage-box a[href*="delete-folder"]');
        $I->see('Удалить каталог и все его содержимое?');

        $I->clickDlgConfirm();
        $I->see('Каталог удален ', '.toast-message');
        self::_openManagersPage($I, false);
        $I->dontSee('folder11');
    }

    public function loadFile(\AcceptanceTester $I)
    {
        self::_openManagersPage($I);

        self::_loadFile($I);

        $I->click('.icon-list a[href="/ru/filemanager?folderId=300"]');
        $I->dontSeeElement('img[alt="Koala.jpg"]');
        $I->clickPopupMenu('folder1');
        $I->wait();
        $I->seeElement('img[alt="Koala.jpg"]');
    }

    public function deleteFile(\AcceptanceTester $I)
    {
        self::_openManagersPage($I);

        self::_loadFile($I);
        $I->moveMouseOver('.icon-list img[alt="Koala.jpg"]');
        $I->click('.manage-box  a[href*="delete-file"]');
        $I->see('Удалить файл?');
        $I->clickDlgConfirm();
        $I->see('Файл удален', '.toast-message');
        $I->dontSeeElement('img[alt="Koala.jpg"]');
    }

    public function renameFile(\AcceptanceTester $I)
    {
        self::_openManagersPage($I);

        self::_loadFile($I);
        $I->moveMouseOver('.icon-list img[alt="Koala.jpg"]');
        $I->click('.manage-box  a[href*="rename-file"]');
        $I->see('Переименовать файл "Koala.jpg"', 'h1');
        $I->seeInField('input[name="LoadedFiles[title]"]', 'Koala.jpg');
        $I->fillField('input[name="LoadedFiles[title]"]', 'Коала');
        $I->click('Сохранить');
        $I->see('Файл "Коала" переименован', '.toast-message');
        $I->dontSeeElement('img[alt="Koala.jpg"]');
        $I->seeElement('img[alt="Коала"]');
    }

    // -----------------------------------------------------------------------------------------------------------------------------------------------

    public static function _loadFile(\AcceptanceTester $I)
    {
        $I->click('Загрузить файлы');
        $I->see('Загрузить файлы', 'h1');

        $I->attachFile('input[type="file"][name="FileManagerLoadForm[uFiles][]"]', 'Koala.jpg');
        $I->wait();
        $I->click('Загрузить', null, 7);

        $I->seeElement('img[alt="Koala.jpg"]');
    }

    public static function _openManagersPage(\AcceptanceTester $I, $auth = true)
    {
        if ($auth) {
            self::login($I);
        }
        $I->amOnPage('filemanager');

        $I->dontSee(404, 'header');
        $I->dontSee(403, 'header');
        $I->see('Файловый менеджер', 'h1');
    }
}
