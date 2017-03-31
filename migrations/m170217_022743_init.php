<?php

namespace yiicms\migrations;

use yii\db\Migration;
use yii\log\DbTarget;
use yii\rbac\DbManager;
use yiicms\components\core\rbac\Rule;
use yiicms\components\users\FileManagerOwnRule;
use yiicms\components\users\ProfileOwnRule;
use yiicms\models\content\Category;
use yiicms\models\content\CategoryPermission;
use yiicms\models\content\Comment;
use yiicms\models\content\Page;
use yiicms\models\content\PageInCategory;
use yiicms\models\content\PageInTag;
use yiicms\models\content\PageRevision;
use yiicms\models\content\Tag;
use yiicms\models\content\UserStat;
use yiicms\models\core\Blocks;
use yiicms\models\core\BlocksForRole;
use yiicms\models\core\BlocksVisibleForPathInfo;
use yiicms\models\core\Crontabs;
use yiicms\models\core\LoadedFiles;
use yiicms\models\core\Mails;
use yiicms\models\core\MailsTemplates;
use yiicms\models\core\Menus;
use yiicms\models\core\MenusForRole;
use yiicms\models\core\MenusVisibleForPathInfo;
use yiicms\models\core\PmailsFolders;
use yiicms\models\core\PmailsIncoming;
use yiicms\models\core\PmailsOutgoing;
use yiicms\models\core\PmailsUserStat;
use yiicms\models\core\Settings;
use yiicms\models\core\Users;
use yiicms\models\core\VFiles;
use yiicms\models\core\VFolders;

class m170217_022743_init extends Migration
{
    public function up()
    {
        $this->createTables();
        $this->registerRules();
        $this->registerPermissions();

        $this->createDefaultRoles();
    }

    public function down()
    {
        $this->dropTables();
        return true;
    }

    private function registerPermissions()
    {
        echo '   > register permissions ...';
        $time = microtime(true);
        /** @var DbManager $auth */
        $auth = \Yii::$app->authManager;

        $forAddItems = [
            'ProfileEditOwn' => ['Редактирование пользователем только своего профиля', ProfileOwnRule::className(), null],
            'ProfileEdit' => ['Редактирование профилей пользователей', null, 'ProfileEditOwn'],

            'FilesManageOwn' => ['Загрузка и управление только своими файлами', FileManagerOwnRule::className(), null],
            'FilesManage' => ['Загрузка и управление файлами', null, 'FilesManageOwn'],

            'Admin' => 'Управление настройками сайта',
            'AdminPermission' => 'Управление правами пользователей',
            'AdminContent' => 'Управление контентом',
        ];

        foreach ($forAddItems as $name => $description) {
            $parentPermission = null;
            /** @var Rule $rule */
            $rule = null;
            if (is_array($description)) {
                list($description, $ruleClass, $parentPermission) = $description;
                if ($ruleClass !== null) {

                    $rule = new $ruleClass;
                    if (null === $auth->getRule($rule->name)) {
                        $auth->add($rule);
                    }
                }
            }
            $perm = $auth->createPermission($name);
            $perm->description = $description;
            if ($rule !== null) {
                $perm->ruleName = $rule->name;
            }
            if ($auth->getPermission($perm->name)) {
                $auth->remove($perm);
            }
            $auth->add($perm);

            if ($parentPermission !== null) {
                $parent = $auth->getPermission($parentPermission);
                if (!$auth->hasChild($parent, $perm)) {
                    $auth->addChild($parent, $perm);
                }
            }
        }

        echo ' done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n";
    }

    private function registerRules()
    {
        echo '    > register rules ...';
        $time = microtime(true);
        /** @var DbManager $auth */
        $auth = \Yii::$app->authManager;
        $fileOwnRule = new FileManagerOwnRule();
        $auth->add($fileOwnRule);
        $profileOwnRule = new ProfileOwnRule();
        $auth->add($profileOwnRule);

        echo ' done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n";
    }

    private function createDefaultRoles()
    {
        echo '    > create roles ...';
        $auth = \Yii::$app->authManager;
        $time = microtime(true);
        $roles = [
            'Registered Users' => ['Зарегестрированный пользователь', 'FilesManageOwn', 'ProfileEditOwn'],
            'Author' => ['Пользователи имеющие право создавать страницы', 'AdminContent'],
            'Super Admin' => [
                'Суперадминистратор обладающий всеми правами',
                'Admin',
                'AdminPermission',
                'ProfileEdit',
                'FilesManage',
            ],
            '__GUEST__' => ['Гости'],
        ];

        $rolesChildren = [
            'Super Admin' => 'Author',
            'Author' => 'Registered Users',
        ];

        $auth = \Yii::$app->authManager;
        foreach ($roles as $roleName => $permissionsNames) {
            $role = $auth->getRole($roleName);
            if ($role === null) {
                $role = $auth->createRole($roleName);
                $role->description = array_shift($permissionsNames);
                $role->data['isGlobal'] = true;
                $auth->add($role);
            }

            /** @noinspection ForeachSourceInspection */
            foreach ($permissionsNames as $permissionName) {
                $permission = $auth->getPermission($permissionName);
                if (null !== $permission && !$auth->hasChild($role, $permission)) {
                    $auth->addChild($role, $permission);
                }
            }
        }

        foreach ($rolesChildren as $roleParentName => $rolesChildName) {
            /** @noinspection NotOptimalIfConditionsInspection */
            if (null !== ($roleParent = $auth->getRole($roleParentName)) &&
                null !== ($roleChild = $auth->getRole($rolesChildName)) &&
                !$auth->hasChild($roleParent, $roleChild)
            ) {
                $auth->addChild($roleParent, $roleChild);
            }
        }

        echo ' done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n";
    }

    private function createTables()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTableUsers($tableOptions);
        $this->createTableSessions($tableOptions);
        $this->createTableRbacRule($tableOptions);
        $this->createTableRbacItem($tableOptions);
        $this->createTableRbacItemChild($tableOptions);
        $this->createTableRbacAssignment($tableOptions);
        $this->createTableSettings($tableOptions);
        $this->createTableLoadedFiles($tableOptions);
        $this->createTableVFolders($tableOptions);
        $this->createTableVFiles($tableOptions);
        $this->createTableMailsTemplates($tableOptions);
        $this->createTableMails($tableOptions);
        $this->createTableCrontabs($tableOptions);
        $this->createTableLog($tableOptions);
        $this->createTableBlocks($tableOptions);
        $this->createTableBlocksForPathInfo($tableOptions);
        $this->createTableBlocksForRole($tableOptions);
        $this->createTableMenus($tableOptions);
        $this->createTableMenusForPathInfo($tableOptions);
        $this->createTableMenusForRole($tableOptions);

        $this->createTablePmailsFolder($tableOptions);
        $this->createTablePmailsIncoming($tableOptions);
        $this->createTablePmailsOutgoing($tableOptions);
        $this->createTablePmailsUserStat($tableOptions);

        $this->createTableContentCategories($tableOptions);
        $this->createTableContentCategoriesPermissions($tableOptions);
        $this->createTableContentTags($tableOptions);
        $this->createTableContentPages($tableOptions);
        $this->createTableContentPagesInCategories($tableOptions);
        $this->createTableContentPagesInTags($tableOptions);
        $this->createTableContentPagesRevision($tableOptions);
        $this->createTableContentComments($tableOptions);
        $this->createTableContentUserStats($tableOptions);
    }

    private function dropTables()
    {
        /** @var DbManager $auth */
        $auth = \Yii::$app->authManager;

        /** @var DbTarget $target */
        $target = \Yii::$app->log->targets['db'];

        $this->dropTable(UserStat::tableName());
        $this->dropTable(Comment::tableName());
        $this->dropTable(PageRevision::tableName());
        $this->dropTable(PageInTag::tableName());
        $this->dropTable(PageInCategory::tableName());
        $this->dropTable(Page::tableName());
        $this->dropTable(Tag::tableName());
        $this->dropTable(CategoryPermission::tableName());
        $this->dropTable(Category::tableName());

        $this->dropTable(PmailsUserStat::tableName());
        $this->dropTable(PmailsOutgoing::tableName());
        $this->dropTable(PmailsIncoming::tableName());
        $this->dropTable(PmailsFolders::tableName());
        $this->dropTable(MenusForRole::tableName());
        $this->dropTable(MenusVisibleForPathInfo::tableName());
        $this->dropTable(Menus::tableName());
        $this->dropTable(BlocksForRole::tableName());
        $this->dropTable(BlocksVisibleForPathInfo::tableName());
        $this->dropTable(Blocks::tableName());
        $this->dropTable($target->logTable);
        $this->dropTable(Crontabs::tableName());
        $this->dropTable(Mails::tableName());
        $this->dropTable(MailsTemplates::tableName());
        $this->dropTable(VFiles::tableName());
        $this->dropTable(VFolders::tableName());
        $this->dropTable(LoadedFiles::tableName());
        $this->dropTable(Settings::tableName());
        $this->dropTable($auth->assignmentTable);
        $this->dropTable($auth->itemChildTable);
        $this->dropTable($auth->itemTable);
        $this->dropTable($auth->ruleTable);
        $this->dropTable('{{%sessions}}');
        $this->dropTable(Users::tableName());
    }

    private function createTableContentUserStats($tableOptions)
    {
        $columns = [
            'userId' => $this->integer()->notNull(),
            'pagesCount' => $this->integer()->defaultValue(0)->notNull(),
            'commentsCount' => $this->integer()->defaultValue(0)->notNull(),
        ];

        $this->createTable(UserStat::tableName(), $columns, $tableOptions);

        $this->addPrimaryKey('user_stat_pk', UserStat::tableName(), 'userId');

        $this->addForeignKey('comment_user_fk', UserStat::tableName(), 'userId', Users::tableName(), 'userId', 'CASCADE');
    }

    private function createTableContentComments($tableOptions)
    {
        $columns = [
            'commentId' => $this->primaryKey(),
            'parentId' => $this->integer()->defaultValue(0)->notNull(),
            'mPath' => $this->string(1000)->defaultValue('')->notNull(),
            'commentGroup' => $this->string(32)->notNull(),
            'createdAt' => $this->dateTime()->notNull(),
            'ownerId' => $this->integer(),
            'ownerLogin' => $this->string(64)->notNull(),
            'commentText' => $this->text()->notNull(),
            'ownerIp' => $this->string(39)->notNull(),
            'lastEditedAt' => $this->dateTime(),
            'lastEditUserId' => $this->integer(),
            'lastEditUserLogin' => $this->string(64),
            'lastEditIp' => $this->string(39),
        ];

        $this->createTable(Comment::tableName(), $columns, $tableOptions);

        $this->addForeignKey('comment_owner_fk', Comment::tableName(), 'ownerId', Users::tableName(), 'userId', 'SET NULL');
        $this->addForeignKey('comment_last_edit_user_fk', Comment::tableName(), 'lastEditUserId', Users::tableName(), 'userId', 'SET NULL');

        if ($this->db->driverName === 'mysql') {
            $this->execute('ALTER TABLE ' . $this->db->quoteTableName(Comment::tableName()) . ' ADD INDEX `comment_mpath_idx` USING BTREE (`mPath`(100))');
        } else {
            $this->createIndex('comment_mpath_idx', Comment::tableName(), 'mPath');
        }

        $this->createIndex('comment_parent_idx', Comment::tableName(), 'parentId');
        $this->createIndex('comment_owner_idx', Comment::tableName(), 'ownerId');
        $this->createIndex('comment_created_at_idx', Comment::tableName(), 'createdAt');
    }

    private function createTableContentPagesRevision($tableOptions)
    {
        $columns = [
            'rowId' => $this->primaryKey(),
            'userId' => $this->integer(),
            'userLogin' => $this->string(64)->notNull(),
            'pageId' => $this->integer()->notNull(),
            'title' => $this->string(215)->notNull(),
            'announce' => $this->text(),
            'pageText' => $this->text()->notNull(),
            'createdAt' => $this->dateTime()->notNull(),
            'userIp' => $this->string(39)->notNull(),
        ];

        $this->createTable(PageRevision::tableName(), $columns, $tableOptions);

        $this->addForeignKey('page_revision_user_fk', PageRevision::tableName(), 'userId', Users::tableName(), 'userId', 'SET NULL');
        $this->addForeignKey('\'page_revision_page_fk', PageRevision::tableName(), 'pageId', Page::tableName(), 'pageId', 'CASCADE');
    }

    private function createTableContentPagesInTags($tableOptions)
    {
        $columns = [
            'pageId' => $this->integer()->notNull(),
            'tagId' => $this->integer()->notNull(),
        ];

        $this->createTable(PageInTag::tableName(), $columns, $tableOptions);

        $this->addPrimaryKey('pit_pk', PageInTag::tableName(), ['pageId', 'tagId']);
        $this->addForeignKey('pit_page_fk', PageInTag::tableName(), 'pageId', Page::tableName(), 'pageId', 'CASCADE');
        $this->addForeignKey('pit_tag_fk', PageInTag::tableName(), 'tagId', Tag::tableName(), 'tagId', 'CASCADE');
    }

    private function createTableContentPagesInCategories($tableOptions)
    {
        $columns = [
            'pageId' => $this->integer()->notNull(),
            'categoryId' => $this->integer()->notNull(),
        ];

        $this->createTable(PageInCategory::tableName(), $columns, $tableOptions);

        $this->addPrimaryKey('pic_pk', PageInCategory::tableName(), ['pageId', 'categoryId']);
        $this->addForeignKey('pic_page_fk', PageInCategory::tableName(), 'pageId', Page::tableName(), 'pageId', 'CASCADE');
        $this->addForeignKey('pic_category_fk', PageInCategory::tableName(), 'categoryId', Category::tableName(), 'categoryId', 'CASCADE');
    }

    private function createTableContentPages($tableOptions)
    {
        $columns = [
            'pageId' => $this->primaryKey(),
            'createdAt' => $this->dateTime()->notNull(),
            'ownerId' => $this->integer(),
            'ownerLogin' => $this->string(64)->notNull(),
            'toFirst' => $this->smallInteger()->defaultValue(0)->notNull(),
            'pageType' => $this->string(128)->notNull(),
            'opened' => $this->smallInteger()->defaultValue(0)->notNull(),
            'slug' => $this->string(235)->notNull(),
            'lang' => $this->string(10)->notNull(),
            'title' => $this->string(200)->notNull(),
            'announce' => $this->text(),
            'pageText' => $this->text()->notNull(),
            'commentsGroup' => $this->string(32),
            'lastEditedAt' => $this->dateTime(),
            'lastUserId' => $this->integer(),
            'lastUserLogin' => $this->string(64),
            'viewCount' => $this->integer()->defaultValue(0)->notNull(),
            'images' => $this->text(),
            'published' => $this->smallInteger()->defaultValue(0)->notNull(),
            'publishedAt' => $this->dateTime()->notNull(),
            'startPublicationDate' => $this->dateTime(),
            'endPublicationDate' => $this->dateTime(),
            'categoriesIds' => $this->text()->notNull(),
            'keywords' => $this->text(),
            'tags' => $this->text(),
        ];

        if ($this->db->driverName === 'pgsql') {
            $columns['images'] = 'JSONB';
            $columns['categoriesIds'] = 'JSONB NOT NULL';
            $columns['tags'] = 'JSONB';
        } elseif ($this->db->driverName === 'mysql') {
            $columns['pageText'] = 'LONGBLOB NOT NULL';
        }

        $this->createTable(Page::tableName(), $columns, $tableOptions);

        $this->addForeignKey('page_last_edit_user_fk', Page::tableName(), 'lastUserId', Users::tableName(), 'userId', 'SET NULL');
        $this->addForeignKey('page_owner_fk', Page::tableName(), 'ownerId', Users::tableName(), 'userId', 'SET NULL');

        $this->createIndex('page_slug_idx', Page::tableName(), ['slug', 'lang']);
        $this->createIndex('page_published_at_idx', Page::tableName(), 'publishedAt');
    }

    private function createTableContentTags($tableOptions)
    {
        $columns = [
            'tagId' => $this->primaryKey(),
            'title' => $this->string(64)->notNull(),
            'pageCount' => $this->integer()->defaultValue(0)->notNull(),
            'slug' => $this->string(255)->notNull(),
        ];

        $this->createTable(Tag::tableName(), $columns, $tableOptions);
    }

    private function createTableContentCategoriesPermissions($tableOptions)
    {
        $columns = [
            'categoryId' => $this->integer()->notNull(),
            'roleName' => $this->string(64)->notNull(),
            'permission' => $this->string(50)->notNull(),
        ];

        $this->createTable(CategoryPermission::tableName(), $columns, $tableOptions);

        $this->addPrimaryKey('category_permission_pk', CategoryPermission::tableName(), ['categoryId', 'roleName', 'permission']);
    }

    private function createTableContentCategories($tableOptions)
    {
        $columns = [
            'categoryId' => $this->primaryKey(),
            'parentId' => $this->integer()->defaultValue(0)->notNull(),
            'mPath' => $this->string(1000)->defaultValue('')->notNull(),
            'weight' => $this->integer()->defaultValue(0)->notNull(),
            'createdAt' => $this->dateTime()->notNull(),
            'titleM' => $this->string(200)->notNull(),
            'description' => $this->text(),
            'slug' => $this->string(235),
            'keywords' => $this->text(),
        ];

        if ($this->db->driverName === 'pgsql') {
            $columns['titleM'] = 'JSONB NOT NULL';
        }

        $this->createTable(Category::tableName(), $columns, $tableOptions);

        if ($this->db->driverName === 'mysql') {
            $this->execute('ALTER TABLE ' . $this->db->quoteTableName(Category::tableName()) . ' ADD INDEX `category_mpath_idx` USING BTREE (`mPath`(100))');
        } else {
            $this->createIndex('category_mpath_idx', Category::tableName(), 'mPath');
        }
        $this->createIndex('category_parent_idx', Category::tableName(), 'parentId');
        $this->createIndex('category_slug_idx', Category::tableName(), ['slug'], true);
    }

    private function createTablePmailsUserStat($tableOptions)
    {
        $columns = [
            'userId' => $this->integer()->notNull(),
            'notReadCount' => $this->integer()->defaultValue(0)->notNull(),
            'totalCount' => $this->integer()->defaultValue(0)->notNull(),
            'subscribe' => $this->smallInteger()->defaultValue(0)->notNull(),
        ];

        $this->createTable(PmailsUserStat::tableName(), $columns, $tableOptions);
        $this->addPrimaryKey('pmails_user_stat_pk', PmailsUserStat::tableName(), 'userId');
        $this->addForeignKey('pmails_user_user_id_fk', PmailsUserStat::tableName(), 'userId', Users::tableName(), 'userId', 'CASCADE');
    }

    private function createTablePmailsOutgoing($tableOptions)
    {
        $columns = [
            'rowId' => $this->primaryKey(),
            'talkId' => $this->string(32)->notNull(),
            'fromUserId' => $this->integer()->notNull(),
            'toUsersList' => $this->text(),
            'trgmToUsers' => $this->text(),
            'subject' => $this->string(255)->notNull(),
            'msgText' => $this->text()->notNull(),
            'sended' => $this->smallInteger()->defaultValue(0)->notNull(),
            'folderId' => $this->integer(),
            'sentAt' => $this->dateTime(),
        ];

        if ($this->db->driverName === 'pgsql') {
            $columns['toUsersList'] = 'JSONB';
        }

        $this->createTable(PmailsOutgoing::tableName(), $columns, $tableOptions);
        $this->addForeignKey('pmails_outgoing_from_user_id_fk', PmailsOutgoing::tableName(), 'fromUserId', Users::tableName(), 'userId', 'CASCADE');
        $this->addForeignKey('pmails_outgoing_folder_fk', PmailsOutgoing::tableName(), 'folderId', PmailsFolders::tableName(), 'folderId', 'CASCADE');
    }

    private function createTablePmailsIncoming($tableOptions)
    {
        $columns = [
            'rowId' => $this->primaryKey(),
            'talkId' => $this->string(32)->notNull(),
            'toUserId' => $this->integer()->notNull(),
            'fromUserId' => $this->integer(),
            'fromUserLogin' => $this->string(64)->notNull(),
            'subject' => $this->string(255)->notNull(),
            'msgText' => $this->text()->notNull(),
            'readed' => $this->smallInteger()->defaultValue(0)->notNull(),
            'folderId' => $this->integer(),
            'sentAt' => $this->dateTime()->notNull(),
        ];

        $this->createTable(PmailsIncoming::tableName(), $columns, $tableOptions);
        $this->addForeignKey('pmails_incoming_to_user_id_fk', PmailsIncoming::tableName(), 'toUserId', Users::tableName(), 'userId', 'CASCADE');
        $this->addForeignKey('pmails_incoming_from_user_id_fk', PmailsIncoming::tableName(), 'fromUserId', Users::tableName(), 'userId', 'SET NULL');
        $this->addForeignKey('pmails_incoming_folder_fk', PmailsIncoming::tableName(), 'folderId', PmailsFolders::tableName(), 'folderId',
            'SET NULL');
    }

    private function createTablePmailsFolder($tableOptions)
    {
        $columns = [
            'folderId' => $this->primaryKey(),
            'userId' => $this->integer()->notNull(),
            'folderType' => $this->smallInteger()->notNull(),
            'title' => $this->string(255)->notNull(),
        ];

        $this->createTable(PmailsFolders::tableName(), $columns, $tableOptions);
        $this->addForeignKey('pmails_folder_user_id_fk', PmailsFolders::tableName(), 'userId', Users::tableName(), 'userId', 'CASCADE');
        $this->createIndex('pmails_folder_user_id_idx', PmailsFolders::tableName(), 'userId');
    }

    private function createTableMenusForRole($tableOptions)
    {
        /** @var DbManager $auth */
        $auth = \Yii::$app->authManager;

        $columns = [
            'menuId' => $this->integer()->notNull(),
            'roleName' => $this->string(64)->notNull(),
        ];

        $this->createTable(MenusForRole::tableName(), $columns, $tableOptions);
        $this->addPrimaryKey('menus_for_role_pk', MenusForRole::tableName(), ['menuId', 'roleName']);
        $this->addForeignKey('menus_for_role_menu_id_fk', MenusForRole::tableName(), 'menuId', Menus::tableName(), 'menuId', 'CASCADE');
        $this->addForeignKey('menus_for_role_role_name_fk', MenusForRole::tableName(), 'roleName', $auth->itemTable, 'name', 'CASCADE');
    }

    private function createTableMenusForPathInfo($tableOptions)
    {
        $columns = [
            'permId' => $this->primaryKey(),
            'menuId' => $this->integer()->notNull(),
            'rule' => $this->string(20)->notNull(),
            'template' => $this->string(255)->notNull(),
        ];

        $this->createTable(MenusVisibleForPathInfo::tableName(), $columns, $tableOptions);
        $this->addForeignKey('menus_for_path_menu_id_fk', MenusVisibleForPathInfo::tableName(), 'menuId', Menus::tableName(), 'menuId', 'CASCADE');
    }

    private function createTableMenus($tableOptions)
    {
        $columns = [
            'menuId' => $this->primaryKey(),
            'parentId' => $this->integer()->notNull(),
            'mPath' => $this->string(1000)->defaultValue('')->notNull(),
            'link' => $this->string(1000)->defaultValue('#')->notNull(),
            'weight' => $this->integer()->defaultValue(0)->notNull(),
            'pathInfoVisibleOrder' => $this->smallInteger()->defaultValue(0)->notNull(),
            'titleM' => $this->text()->notNull(),
            'subTitleM' => $this->text(),
            'icon' => $this->string(32),
        ];

        if ($this->db->driverName === 'pgsql') {
            $columns['titleM'] = 'JSONB NOT NULL';
            $columns['subTitleM'] = 'JSONB';
        }

        $this->createTable(Menus::tableName(), $columns, $tableOptions);
        if ($this->db->driverName === 'mysql') {
            $this->execute('ALTER TABLE ' . $this->db->quoteTableName(Menus::tableName()) . ' ADD INDEX `menus_mpath_idx` USING BTREE (`mPath`(100))');
        } else {
            $this->createIndex('menus_mpath_idx', Menus::tableName(), 'mPath');
        }
    }

    private function createTableBlocksForRole($tableOptions)
    {
        /** @var DbManager $auth */
        $auth = \Yii::$app->authManager;

        $columns = [
            'blockId' => $this->integer()->notNull(),
            'roleName' => $this->string(64)->notNull(),
        ];

        $this->createTable(BlocksForRole::tableName(), $columns, $tableOptions);
        $this->addPrimaryKey('blocks_for_role_pk', BlocksForRole::tableName(), ['blockId', 'roleName']);
        $this->addForeignKey('blocks_for_role_block_id_fk', BlocksForRole::tableName(), 'blockId', Blocks::tableName(), 'blockId', 'CASCADE');
        $this->addForeignKey('blocks_for_role_role_name_fk', BlocksForRole::tableName(), 'roleName', $auth->itemTable, 'name', 'CASCADE');
    }

    private function createTableBlocksForPathInfo($tableOptions)
    {
        $columns = [
            'permId' => $this->primaryKey(),
            'blockId' => $this->integer()->notNull(),
            'rule' => $this->string(20)->notNull(),
            'template' => $this->string(255)->notNull(),
        ];

        $this->createTable(BlocksVisibleForPathInfo::tableName(), $columns, $tableOptions);
        $this->addForeignKey('blocks_for_path_block_id_fk', BlocksVisibleForPathInfo::tableName(), 'blockId', Blocks::tableName(), 'blockId',
            'CASCADE');
    }

    private function createTableBlocks($tableOptions)
    {
        $columns = [
            'blockId' => $this->primaryKey(),
            'description' => $this->text(),
            'titleM' => $this->text()->notNull(),
            'position' => $this->string(255)->notNull(),
            'weight' => $this->integer()->defaultValue(0)->notNull(),
            'activy' => $this->smallInteger()->defaultValue(1)->notNull(),
            'params' => $this->text(),
            'contentClass' => $this->string(255)->notNull(),
            'pathInfoVisibleOrder' => $this->smallInteger()->defaultValue(0)->notNull(),
            'viewFile' => $this->string(255),
            'trgmIndex' => $this->text(),
        ];

        if ($this->db->driverName === 'pgsql') {
            $columns['titleM'] = 'JSONB NOT NULL';
            $columns['params'] = 'JSONB';
        }

        $this->createTable(Blocks::tableName(), $columns, $tableOptions);
    }

    private function createTableMailsTemplates($tableOptions)
    {
        $columns = [
            'templateId' => $this->string(40)->notNull(),
            'description' => $this->text()->notNull(),
            'templateM' => $this->text()->notNull(),
            'subjectM' => $this->text()->notNull(),
        ];

        if ($this->db->driverName === 'pgsql') {
            $columns['templateM'] = 'JSONB NOT NULL';
            $columns['subjectM'] = 'JSONB NOT NULL';
        }

        $this->createTable(MailsTemplates::tableName(), $columns, $tableOptions);
        $this->addPrimaryKey('mail_templates_pk', MailsTemplates::tableName(), 'templateId');
    }

    private function createTableLog($tableOptions)
    {
        /** @var DbTarget $target */
        $target = \Yii::$app->log->targets['db'];

        $columns = [
            'id' => $this->bigPrimaryKey(),
            'level' => $this->integer(),
            'category' => $this->string(255),
            'log_time' => $this->double(),
            'prefix' => $this->text(),
            'message' => $this->text(),
        ];

        $this->createTable($target->logTable, $columns, $tableOptions);
        $this->createIndex('log_category_idx', $target->logTable, 'category');
        $this->createIndex('log_level_idx', $target->logTable, 'level');
    }

    private function createTableCrontabs($tableOptions)
    {
        $columns = [
            'jobClass' => $this->string(255)->notNull(),
            'runTime' => $this->string(60),
            'descript' => $this->string(255),
            'lastRunStart' => $this->dateTime(),
            'lastRunStop' => $this->dateTime(),

        ];

        $this->createTable(Crontabs::tableName(), $columns, $tableOptions);
        $this->addPrimaryKey('crontabs_pk', Crontabs::tableName(), 'jobClass');
    }

    private function createTableMails($tableOptions)
    {
        $columns = [
            'mailId' => $this->bigPrimaryKey(),
            'toLogin' => $this->string(64)->notNull(),
            'email' => $this->string(255)->notNull(),
            'subject' => $this->string(255)->notNull(),
            'messageText' => $this->text()->notNull(),
            'sentAt' => $this->dateTime(),
            'backendId' => $this->string(32),
            'createdAt' => $this->dateTime()->notNull(),
            'fromUserId' => $this->integer()->notNull(),
        ];

        $this->createTable(Mails::tableName(), $columns, $tableOptions);
        $this->createIndex('mails_backendid_idx', Mails::tableName(), 'backendId');
    }

    private function createTableVFiles($tableOptions)
    {
        $columns = [
            'folderId' => $this->integer()->notNull(),
            'fileId' => $this->string(64)->notNull(),
        ];

        $this->createTable(VFiles::tableName(), $columns, $tableOptions);
        $this->addPrimaryKey('vfiles_pk', VFiles::tableName(), ['folderId', 'fileId']);
        $this->addForeignKey('vfiles_folder_fk', VFiles::tableName(), 'folderId', VFolders::tableName(), 'folderId', 'CASCADE');
        $this->addForeignKey('vfiles_file_fk', VFiles::tableName(), 'fileId', LoadedFiles::tableName(), 'id', 'CASCADE');
    }

    private function createTableVFolders($tableOptions)
    {
        $columns = [
            'folderId' => $this->primaryKey(),
            'parentId' => $this->integer()->notNull(),
            'title' => $this->string(255)->notNull(),
            'mPath' => $this->string(1000)->defaultValue('')->notNull(),
            'userId' => $this->integer()->notNull(),
        ];

        $this->createTable(VFolders::tableName(), $columns, $tableOptions);
        $this->addForeignKey('vfolders_user_fk', VFolders::tableName(), 'userId', Users::tableName(), 'userId');
        if ($this->db->driverName === 'mysql') {
            $this->execute('ALTER TABLE ' . $this->db->quoteTableName(VFolders::tableName()) . ' ADD INDEX `vfolders_mPath_idx` USING BTREE (`mPath`(100))');
        } else {
            $this->createIndex('vfolders_mPath_idx', VFolders::tableName(), 'mPath', true);
        }
    }

    private function createTableLoadedFiles($tableOptions)
    {
        $columns = [
            'id' => $this->string(64)->notNull(),
            'path' => $this->string(255)->notNull(),
            'title' => $this->string(255)->notNull(),
            'createdAt' => $this->dateTime()->notNull(),
            'size' => $this->integer()->notNull(),
            'persistent' => $this->smallInteger()->defaultValue(0)->notNull(),
            'userId' => $this->integer()->notNull(),
            'public' => $this->smallInteger()->defaultValue(1)->notNull(),

        ];

        $this->createTable(LoadedFiles::tableName(), $columns, $tableOptions);
        $this->addPrimaryKey('loaded_files_pk', LoadedFiles::tableName(), 'id');
        $this->addForeignKey('loaded_files_user_fk', LoadedFiles::tableName(), 'userId', Users::tableName(), 'userId');
    }

    private function createTableSettings($tableOptions)
    {
        $columns = [
            'paramName' => $this->string(100)->notNull(),
            'value' => 'LONGTEXT',
        ];

        if ($this->db->driverName === 'pgsql') {
            $columns['value'] = 'JSONB';
        }

        $this->createTable(Settings::tableName(), $columns, $tableOptions);
        $this->addPrimaryKey('settings_pk', Settings::tableName(), 'paramName');
    }

    private function createTableRbacAssignment($tableOptions)
    {
        /** @var DbManager $auth */
        $auth = \Yii::$app->authManager;

        $columns = [
            'item_name' => $this->string(64)->notNull(),
            'user_id' => $this->integer()->notNull(),
            'created_at' => $this->integer(),
        ];
        $this->createTable($auth->assignmentTable, $columns, $tableOptions);
        $this->addPrimaryKey('rbacAssignment_pk', $auth->assignmentTable, ['item_name', 'user_id']);

        $this->addForeignKey('assignment_item_fk', $auth->assignmentTable, 'item_name', $auth->itemTable, 'name', 'CASCADE', 'CASCADE');
        $this->addForeignKey('assignment_user_fk', $auth->assignmentTable, 'user_id', Users::tableName(), 'userId', 'CASCADE', null);
    }

    private function createTableRbacRule($tableOptions)
    {
        /** @var DbManager $auth */
        $auth = \Yii::$app->authManager;

        $columns = [
            'name' => $this->string(64)->notNull(),
            'data' => $this->text(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),

        ];

        $this->createTable($auth->ruleTable, $columns, $tableOptions);
        $this->addPrimaryKey('rbacRule_pk', $auth->ruleTable, 'name');
    }

    private function createTableRbacItemChild($tableOptions)
    {
        /** @var DbManager $auth */
        $auth = \Yii::$app->authManager;

        $columns = [
            'parent' => $this->string(64),
            'child' => $this->string(64),
        ];

        $this->createTable($auth->itemChildTable, $columns, $tableOptions);
        $this->addPrimaryKey('rbacItemChild_pk', $auth->itemChildTable, ['parent', 'child']);
        $this->addForeignKey('item_parent_fk', $auth->itemChildTable, 'parent', $auth->itemTable, 'name', 'CASCADE', 'CASCADE');
        $this->addForeignKey('item_child_fk', $auth->itemChildTable, 'child', $auth->itemTable, 'name', 'CASCADE', 'CASCADE');
    }

    private function createTableRbacItem($tableOptions)
    {
        /** @var DbManager $auth */
        $auth = \Yii::$app->authManager;

        $columns = [
            'name' => $this->string(64)->notNull(),
            'type' => $this->smallInteger()->notNull(),
            'description' => $this->text(),
            'rule_name' => $this->string(64),
            'data' => $this->text(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),

        ];

        $this->createTable($auth->itemTable, $columns, $tableOptions);
        $this->addPrimaryKey('rbacItem_pk', $auth->itemTable, 'name');
        $this->addForeignKey('rule_fk', $auth->itemTable, 'rule_name', '{{%rbacRule}}', 'name', 'SET NULL', 'CASCADE');
        $this->createIndex('type_idx', $auth->itemTable, 'type');
    }

    private function createTableSessions($tableOptions)
    {
        $columns = [
            'id' => $this->string(128)->notNull(),
            'expire' => $this->integer()->notNull(),
            'data' => 'LONGBLOB',
        ];

        if ($this->db->driverName === 'pgsql') {
            $columns['data'] = 'BYTEA';
        }

        $this->createTable('{{%sessions}}', $columns, $tableOptions);
        $this->addPrimaryKey('session_pk', '{{%sessions}}', 'id');
    }

    private function createTableUsers($tableOptions)
    {
        $columns = [
            'userId' => $this->primaryKey(),
            'login' => $this->string(64)->unique()->notNull(),
            'password' => $this->string(64)->notNull(),
            'email' => $this->string(255)->unique()->notNull(),
            'sex' => $this->smallInteger()->defaultValue(-1)->notNull(),
            'fio' => $this->string(200),
            'timeZone' => $this->string(30)->defaultValue('UTC')->notNull(),
            'birthday' => $this->date(),
            'lang' => $this->string(6),
            'phone' => $this->string(12),
            'lastIp' => $this->string(39)->notNull(),
            'provider' => $this->string(80),
            'providerIdentitly' => $this->string(200),
            'uData' => $this->text(),
            'createdAt' => $this->dateTime()->notNull(),
            'updatedAt' => $this->dateTime(),
            'visitedAt' => $this->dateTime()->notNull(),
            'status' => $this->smallInteger()->notNull(),
            'token' => $this->string(64)->notNull(),
            'photo' => $this->text(),
            'authKeys' => $this->text(),
            'social' => $this->text(),
            'uploadedFilesSize' => $this->bigInteger()->defaultValue(0)->notNull(),
        ];

        if ($this->db->driverName === 'pgsql') {
            $columns['uData'] = 'JSONB';
            $columns['photo'] = 'JSONB';
            $columns['authKeys'] = 'JSONB';
            $columns['social'] = 'JSONB';
        }

        $this->createTable(Users::tableName(), $columns, $tableOptions);
        $this->createIndex('user_provider', Users::tableName(), ['provider', 'providerIdentitly'], true);
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
