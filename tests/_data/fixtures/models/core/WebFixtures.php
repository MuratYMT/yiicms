<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 25.11.2016
 * Time: 10:23
 */

namespace yiicms\tests\_data\fixtures\models\core;

use yiicms\tests\_data\fixtures\models\content\CategoryFixture;
use yiicms\tests\_data\fixtures\models\content\CategoryPermissionFixture;
use yiicms\tests\_data\fixtures\models\content\PageFixture;
use yiicms\tests\_data\fixtures\models\content\PageInCategoryFixture;
use yiicms\tests\_data\fixtures\models\content\PageInTagFixture;
use yiicms\tests\_data\fixtures\models\content\TagFixture;
use yii\test\Fixture;

class WebFixtures extends Fixture
{
    public function __construct(array $config = [])
    {
        $this->depends = [
            'settings' => SettingsFixture::className(),
            'files' => VFilesFixture::className(),
            'loadedFiles' => LoadedFilesFixture::className(),
            'folders' => VFoldersFixture::className(),
            'user' => UsersFixture::className(),
            'role' => RoleFixture::className(),
            'permission' => PermissionFixture::className(),
            'pmail' => PmailsFixtures::className(),
            'menus' => MenusFixture::className(),
            'menusForRole' => MenusForRoleFixture::className(),
            'menusForPathInfo' => MenusForPathInfoFixture::className(),
            'blocks' => BlocksFixture::className(),
            'blocksForRole' => BlocksForRoleFixture::className(),
            'blocksForPathInfo' => BlocksForPathInfoFixture::className(),
            'crontabs' => CrontabFixture::className(),
            'categories' => CategoryFixture::className(),
            'categoriesPermission' => CategoryPermissionFixture::className(),
            'tags' => TagFixture::className(),
            'pages' => PageFixture::className(),
            'pageInCategory' => PageInCategoryFixture::className(),
            'pageInTag' => PageInTagFixture::className(),
            'feedBack' => FeedBackFixture::className(),
        ];
        parent::__construct($config);
    }
}
