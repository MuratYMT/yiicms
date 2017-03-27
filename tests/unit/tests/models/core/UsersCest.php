<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 18.05.2016
 * Time: 8:07
 */

namespace common\unit\test\models\web;

use yiicms\components\core\AuthKey;
use yiicms\components\core\DateTime;
use yiicms\components\core\File;
use yiicms\models\core\Settings;
use yiicms\models\core\Users;
use yiicms\tests\_data\fixtures\models\core\RoleFixture;
use yiicms\tests\_data\fixtures\models\core\UsersFixture;
use tests\unit\UnitCest;
use yii\db\Query;

class UsersCest extends UnitCest
{
    public static function _cestFixtures()
    {
        return [
            'user' => UsersFixture::className(),
            'roles' => RoleFixture::className(),
        ];
    }

    public function testUData(\MyUnitTester $I)
    {
        $user = Users::findById(-1);
        $user->uData = [];

        $I->assertEquals([], $user->uData);
        $user->setUserData('key1', 'value1');
        $I->assertEquals(['key1' => 'value1'], $user->uData);
        $user->setUserData('key2', 'value2');
        $I->assertEquals(['key1' => 'value1', 'key2' => 'value2'], $user->uData);

        $I->assertEquals('value1', $user->getUserData('key1', '1234'));
        $I->assertEquals('value2', $user->getUserData('key2', '1234'));
        $I->assertEquals('value1', $user->extractUserData('key1', '1234'));
        $I->assertEquals('1234', $user->getUserData('key1', '1234'));
        $I->assertEquals('1234', $user->extractUserData('key1', '1234'));
        $I->assertEquals(['key2' => 'value2'], $user->uData);

        $user->deleteUserData('key2');
        $I->assertEquals('1234', $user->getUserData('key2', '1234'));
        $I->assertEquals([], $user->uData);
    }

    public function testFindIdentity(\MyUnitTester $I)
    {
        $user = Users::findById(-1);

        $formatter = \Yii::$app->formatter;
        $formatter->locale = 'en';
        $timeZone = 'UTC';
        \Yii::$app->timeZone = $timeZone;
        $formatter->timeZone = $timeZone;

        Users::findIdentity(-1);

        $I->assertEquals($user->lang, \Yii::$app->formatter->locale);
        $I->assertEquals($user->timeZone, \Yii::$app->formatter->timeZone);
        $I->assertEquals($user->timeZone, \Yii::$app->timeZone);
    }

    public function testAuthKey(\MyUnitTester $I)
    {
        $user = Users::findById(-1);
        Settings::set('users.multiLogin', 0);

        $user->authKeys = [];
        $user->getAuthKey();
        $authKeys = $user->authKeys;
        $key1 = reset($authKeys)->key;

        $fromDb = AuthKey::createFormJson((new Query())
            ->select(['authKeys'])
            ->from(Users::tableName())
            ->where(['userId' => -1])
            ->scalar());

        $I->assertEquals($authKeys, $fromDb);
        $user2 = Users::findById(-1);
        $I->assertEquals($authKeys, $user2->authKeys);

        $I->assertTrue($user2->validateAuthKey(reset($authKeys)->key));
        $I->assertFalse($user2->validateAuthKey(reset($authKeys)->key . '2'));

        // множественная залогиненость должна отсутствовать
        $user3 = Users::findById(-1);
        $user3->getAuthKey();
        $authKeys = $user3->authKeys;
        $I->assertCount(1, $authKeys);
        $key2 = reset($authKeys)->key;

        $I->assertNotEquals($key1, $key2);
        $I->assertTrue($user3->validateAuthKey($key2));
        $I->assertFalse($user3->validateAuthKey($key1));

        Settings::set('users.multiLogin', 1);
        $user = Users::findById(-1);
        $user->authKeys = [];
        $user->getAuthKey();

        $user2 = Users::findById(-1);
        $user2->getAuthKey();
        $authKeys2 = $user2->authKeys;

        $I->assertCount(2, $authKeys2);
        $authKeys11 = array_shift($authKeys2);
        $authKeys12 = array_shift($authKeys2);

        $key1 = $authKeys11->key;
        $key2 = $authKeys12->key;

        $I->assertNotEquals($key1, $key2);
        $I->assertTrue($user2->validateAuthKey($key2));
        $I->assertTrue($user2->validateAuthKey($key1));

        //истечение срока
        Settings::set('users.multiLogin', 1);
        $user = Users::findById(-1);
        $user->authKeys = [];
        $user->getAuthKey();
        $authKeys = $user->authKeys;
        $key = reset($authKeys)->key;

        $I->assertTrue($user->validateAuthKey($key));

        $authKeys[$key]->expire = time() - 1;
        $user->authKeys = $authKeys;

        $I->assertFalse($user->validateAuthKey($key));
    }

    public function testSocial(\MyUnitTester $I)
    {
        $user = Users::findById(-1);
        $user->social = [];
        $I->assertEquals([], $user->social);

        $user->facebook = 'value1';
        $I->assertEquals(['facebook' => 'value1'], $user->social);
        $user->vkontakte = 'value2';
        $I->assertEquals(['facebook' => 'value1', 'vkontakte' => 'value2'], $user->social);

        $I->assertEquals('value1', $user->facebook);
        $I->assertEquals('value2', $user->vkontakte);
        $user->facebook = null;
        $I->assertNull($user->facebook);
        $I->assertEquals(['vkontakte' => 'value2'], $user->social);
    }

    public function testToken(\MyUnitTester $I)
    {
        $user = Users::findById(-1);
        $user->generateToken();
        $token = $user->token;
        $fromDb = (new Query())
            ->select(['token'])
            ->from(Users::tableName())
            ->where(['userId' => -1])
            ->scalar();

        $I->assertEquals($token, $fromDb);
        $user2 = Users::findById(-1);
        $I->assertEquals($token, $user2->token);

        $user3 = Users::findIdentityByAccessToken($token);
        $I->assertNotNull($user3);
        $I->assertEquals($user->userId, $user3->userId);
    }

    public function testFind(\MyUnitTester $I)
    {
        $user = Users::findByLogin('SXSXS');
        $I->assertNotNull($user);
        $I->assertEquals(220, $user->userId);

        $user = Users::findByLogin('NOT_ACTIVE');
        $I->assertNull($user);

        $user = Users::findById(220);
        $I->assertNotNull($user);
        $I->assertEquals(220, $user->userId);

        $user = Users::findById(10);
        $I->assertNull($user);

        $user = Users::findByIdWithAllStatus(10);
        $I->assertNotNull($user);
        $I->assertEquals(10, $user->userId);

        $user = Users::findByEmail('murat_ymt@mail.ru');
        $I->assertNotNull($user);
        $I->assertEquals(-1, $user->userId);
    }

    public function testPasswordValidate(\MyUnitTester $I)
    {
        $user = Users::findById(-1);

        $I->assertTrue($user->validatePassword('SuperUser'));
        $I->assertFalse($user->validatePassword('admin2'));
    }

    public function testUnique(\MyUnitTester $I)
    {
        $user = new Users();

        $user->login = 'SXSXS';
        $user->validate(['login']);
        $I->assertTrue($user->hasErrors('login'));

        $user->login = 'SXSXZ';
        $user->validate(['login']);
        $I->assertFalse($user->hasErrors('login'));

        $user->email = 'murat_ymt@mail.ru';
        $user->validate(['email']);
        $I->assertTrue($user->hasErrors('email'));

        $user->email = 'murat_ymt2@mail.ru';
        $user->validate(['email']);
        $I->assertFalse($user->hasErrors('email'));
    }

    public function testBirthDay(\MyUnitTester $I)
    {
        $user = new Users();

        $user->birthday = new DateTime('2010-05-05', 'UTC');

        $I->assertEquals('2010-05-05', $user->getAttribute('birthday'));
    }

    public function testPhoto(\MyUnitTester $I)
    {
        $file = new File(['id' => 12345]);
        $user = new Users();
        $user->photo = $file;

        $I->assertEquals(File::saveToJson($file), $user->getAttribute('photo'));
    }

    public function testRegister(\MyUnitTester $I)
    {
        $user = new Users();
        $user->scenario = Users::SC_REGISTRATION;
        $user->load(
            ['zz' =>
                ['login' => 'qaz', 'email' => 'murat_ymt3@mail.ru', 'timeZone' => 'Asia/Almaty', 'phone' => '+77017775566', 'password' => 'SuperUser']],
            'zz'
        );
        $I->assertTrue($user->save());
    }
}
