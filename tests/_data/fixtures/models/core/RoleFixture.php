<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 23.05.2016
 * Time: 16:49
 */

namespace yiicms\tests\_data\fixtures\models\core;

use yii\test\Fixture;
use yiicms\models\core\Settings;

class RoleFixture extends Fixture
{
    public function __construct(array $config = [])
    {
        $this->depends = [UsersFixture::className()];
        parent::__construct($config);
    }

    public function load()
    {
        $auth = \Yii::$app->authManager;

        $role = $auth->createRole(Settings::get('users.defaultGuestRole'));
        $auth->add($role);

        $role1 = $auth->createRole('role1');
        $auth->add($role1);
        $auth->assign($role1, -1);

        $role11 = $auth->createRole('role11');
        $auth->add($role11);
        $auth->addChild($role1, $role11);

        $role12 = $auth->createRole('role12');
        $auth->add($role12);
        $auth->addChild($role1, $role12);

        $role111 = $auth->createRole('role111');
        $auth->add($role111);
        $auth->addChild($role11, $role111);

        $role = $auth->createRole('role2');
        $auth->add($role);
        $auth->assign($role, 220);

        $role = $auth->createRole('role3');
        $auth->add($role);
        $auth->assign($role, 220);

        $role = $auth->createRole('role4');
        $auth->add($role);

        $role = $auth->createRole(Settings::get('users.defaultRegisteredRole'));
        $auth->add($role);
        $auth->assign($role, -1);
        $auth->assign($role, 220);

        $role = $auth->createRole('Super Admin');
        $auth->add($role);
        $auth->assign($role, -1);
        $auth->assign($role, 220);
    }

    public function unload()
    {
        $auth = \Yii::$app->authManager;

        foreach ($auth->getPermissions() as $perm) {
            $auth->remove($perm);
        }
        foreach ($auth->getRoles() as $role) {
            $auth->remove($role);
        }
    }
}
