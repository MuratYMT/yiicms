<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 31.05.2016
 * Time: 9:48
 */

namespace yiicms\tests\_data\fixtures\models\core;

use yii\test\Fixture;
use yiicms\components\users\FileManagerOwnRule;
use yiicms\components\users\ProfileOwnRule;
use yiicms\models\core\Settings;

class PermissionFixture extends Fixture
{
    public function __construct(array $config = [])
    {
        $this->depends = [RoleFixture::className(), RuleFixture::className()];
        parent::__construct($config);
    }

    public function load()
    {
        $auth = \Yii::$app->authManager;
        $perm = $auth->createPermission('perm11');
        $auth->add($perm);
        $auth->addChild($auth->getRole('role1'), $perm);

        $perm = $auth->createPermission('perm12');
        $auth->add($perm);
        $auth->addChild($auth->getRole('role1'), $perm);

        $perm = $auth->createPermission('perm111');
        $auth->add($perm);
        $auth->addChild($auth->getRole('role11'), $perm);

        $perm = $auth->createPermission('perm21');
        $auth->add($perm);
        $auth->addChild($auth->getRole('role2'), $perm);

        $perm = $auth->createPermission('perm31');
        $auth->add($perm);
        $auth->addChild($auth->getRole('role3'), $perm);

        $perm = $auth->createPermission('perm101');
        $auth->add($perm);
        $perm = $auth->createPermission('perm102');
        $auth->add($perm);

        $perm = $auth->createPermission('Admin');
        $auth->add($perm);
        $auth->addChild($auth->getRole('Super Admin'), $perm);

        $perm = $auth->createPermission('AdminPermission');
        $auth->add($perm);
        $auth->addChild($auth->getRole('Super Admin'), $perm);

        $perm = $auth->createPermission('AdminContent');
        $auth->add($perm);
        $auth->addChild($auth->getRole('Super Admin'), $perm);

        //доступ к управлению профайлом
        $permProf = $auth->createPermission('ProfileEdit');
        $auth->add($permProf);

        $permProfOwn = $auth->createPermission('ProfileEditOwn');

        $permProfOwn->ruleName = (new ProfileOwnRule)->name;
        $auth->add($permProfOwn);
        $auth->addChild($permProfOwn, $permProf);
        $auth->addChild($auth->getRole(Settings::get('users.defaultRegisteredRole')), $permProfOwn);

        //доступ к файловоому менеджеру

        $fileManage = $auth->createPermission('FilesManage');
        $auth->add($fileManage);

        $fileManageOwn = $auth->createPermission('FilesManageOwn');
        $fileManageOwn->ruleName = (new FileManagerOwnRule())->name;
        $auth->add($fileManageOwn);
        $auth->addChild($fileManageOwn, $fileManage);
        $auth->addChild($auth->getRole(Settings::get('users.defaultRegisteredRole')), $fileManageOwn);
    }

    public function unload()
    {
        $auth = \Yii::$app->authManager;
        foreach ($auth->getPermissions() as $permission) {
            $auth->remove($permission);
        }
    }
}
