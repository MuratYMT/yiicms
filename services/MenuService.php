<?php
/**
 * Created by PhpStorm.
 * User: murat
 * Date: 03.09.2017
 * Time: 23:10
 */

namespace yiicms\services;

use yii\base\InvalidParamException;
use yiicms\components\core\ArrayHelper;
use yiicms\components\core\Helper;
use yiicms\components\core\RbacHelper;
use yiicms\components\core\TreeHelper;
use yiicms\components\YiiCms;
use yiicms\models\core\Menus;
use yiicms\models\core\MenusForRole;
use yiicms\models\core\MenusVisibleForPathInfo;

class MenuService
{
    use VisibleForPathTrait;

    /**
     * предоставляет роли видимость пункта меню
     * @param Menus $menu
     * @param string $roleName имя роли
     * @param bool $recursive предоставлять ли дочерним пунктам меню
     * @return bool
     * @throws \Exception
     */
    public function grant(Menus $menu, $roleName, $recursive = false)
    {
        $trans = YiiCms::$app->getDb()->beginTransaction();
        try {
            $mfr = MenusForRole::findOne(['roleName' => $roleName, 'menuId' => $menu->menuId]);
            if ($mfr === null) {
                $mfr = new MenusForRole(['roleName' => $roleName, 'menuId' => $menu->menuId]);
            }
            $result = $mfr->save();

            if (!$result) {
                $trans->rollBack();
                return false;
            }

            if ($recursive) {
                $childesList = $menu->childrenBranch;
                foreach ($childesList as $child) {
                    if (!$this->grant($child, $roleName, $recursive)) {
                        $trans->rollBack();
                        return false;
                    }
                }
            }
            $trans->commit();
            return true;
        } catch (\Exception $e) {
            $trans->rollBack();
            throw $e;
        }
    }

    /**
     * отменяет у роли видимость пункта меню
     * @param Menus $menu
     * @param string $roleName имя роли
     * @param bool $recursive отменять ли к дочерним пунктам меню
     * @return bool
     * @throws \Exception
     */
    public function revoke(Menus $menu, $roleName, $recursive = false)
    {
        $trans = YiiCms::$app->getDb()->beginTransaction();
        try {
            $result = false;
            $mfr = MenusForRole::findOne(['roleName' => $roleName, 'menuId' => $menu->menuId]);
            if ($mfr !== null) {
                $result = $mfr->delete();
            }

            if ($result === false) {
                $trans->rollBack();
                return false;
            }
            if ($recursive) {
                $childsList = $menu->childrenBranch;
                foreach ($childsList as $child) {
                    if (!$this->revoke($child, $roleName, $recursive)) {
                        $trans->rollBack();
                        return false;
                    }
                }
            }
            $trans->commit();
            return true;
        } catch (\Exception $e) {
            $trans->rollBack();
            throw $e;
        }
    }

    /**
     * массив всех пунктов меню отсортированных в порядке обхода по дереву
     * @return Menus[]
     */
    public function allMenus()
    {
        $menusRaw = Menus::find()
            ->distinct()
            ->orderBy(['weight' => SORT_ASC, 'menuId' => SORT_ASC])
            ->asArray()
            ->all();

        $menusRaw = TreeHelper::build($menusRaw, 'menuId', 'weight');

        return Helper::populateArray(Menus::class, $menusRaw);
    }

    /**
     * загружает пункты меню из указанного блока доступные для текущего пользователя. корневой пункт в
     * результат не включается
     * @param $userId integer Для какого пользователя строится меню
     * @param $rootMenuId int корневой пункт меню пункты которого надо выдать
     * @return Menus[]
     * @throws InvalidParamException
     */
    public function branchForUser($userId, $rootMenuId)
    {
        $roles = ArrayHelper::getColumn(RbacHelper::rolesRecursiveForUser($userId), 'name');
        /** @var Menus $rootMenu */
        $rootMenu = Menus::find()
            ->innerJoinWith('menusForRole')
            ->where([Menus::tableName() . '.[[menuId]]' => $rootMenuId])
            ->andWhere([MenusForRole::tableName() . '.[[roleName]]' => $roles])
            ->one();
        if (null === $rootMenu) {
            return [];
        }

        $menusRaw = Menus::find()->distinct()
            ->innerJoinWith('menusForRole')
            ->where(['like', 'mPath', $rootMenu->mPath . '%', false])
            ->andWhere([MenusForRole::tableName() . '.[[roleName]]' => $roles])
            ->andWhere(['<>', Menus::tableName() . '.[[menuId]]', $rootMenuId])
            ->asArray()
            ->indexBy('menuId')
            ->all();
        YiiCms::$app->menuService->clearObjectsForThisPage(
            $menusRaw,
            MenusVisibleForPathInfo::primaryKey(),
            MenusVisibleForPathInfo::find()->all()
        );
        $menusRaw = TreeHelper::build($menusRaw, 'menuId', 'weight');

        return Helper::populateArray(Menus::class, $menusRaw);
    }

    public function save(Menus $menu)
    {
        $trans = YiiCms::$app->getDb()->beginTransaction();
        try {
            $isNewRecord = $menu->isNewRecord;
            if (!$isNewRecord) {
                TreeHelper::updateHierarchicalData($menu);
            }
            $result = $menu->save();

            if ($result) {
                if ($isNewRecord) {
                    //устанавливаем видимость для пункта меню как у родительского
                    TreeHelper::setMPath($menu);
                    $this->visibleForRolesAsParent($menu);
                }
                $trans->commit();
            } else {
                $trans->rollBack();
            }
            return $result;
        } catch (\Exception $e) {
            $trans->rollBack();
            throw $e;
        }
    }

    public function delete(Menus $menu, $recursive = false)
    {
        $trans = YiiCms::$app->getDb()->beginTransaction();
        try {
            $count = 0;
            if ($recursive) {
                //удаляем дочерние
                foreach ($menu->children as $child) {
                    $result = $this->delete($child, $recursive);
                    if ($result === false) {
                        $trans->rollBack();
                        return false;
                    }
                    $count += $result;
                }
            } else {
                //перемещаем дочерние категории в родительскую категорию
                $parentId = $menu->parentId;
                foreach ($menu->children as $child) {
                    $child->parentId = $parentId;
                    if (!$this->save($child)) {
                        $trans->rollBack();
                        return false;
                    }
                }
            }
            unset($menu->children);

            $result = $menu->delete();
            if ($result) {
                $trans->commit();
                $count += $result;
                return $count;
            }
            $trans->rollBack();
            return false;
        } catch (\Exception $e) {
            $trans->rollBack();
            throw $e;
        }
    }

    /**
     * устанавливает в дочерней ветке видимость для ролей как у текущего элемента
     * @param Menus $menu
     * @return bool
     */
    public function replaceChildrenVisibleForRole(Menus $menu)
    {
        $trans = YiiCms::$app->getDb()->beginTransaction();
        try {
            foreach ($menu->children as $child) {
                if (!$this->visibleForRolesAsParent($child) || !$this->replaceChildrenVisibleForRole($child)) {
                    $trans->rollBack();
                    return false;
                }
            }
            $trans->commit();
            return true;
        } catch (\Exception $e) {
            $trans->rollBack();
            return false;
        }
    }

    /**
     * устанавливает видимость пукта меню для ролей как у родительского
     * @param Menus $menu
     * @return bool
     */
    public function visibleForRolesAsParent($menu)
    {
        $db = YiiCms::$app->getDb();
        $menuId = $menu->menuId;
        if ($menu->parentId === 0) {
            return true;
        }
        $transaction = $db->beginTransaction();
        try {
            $db->createCommand()
                ->delete(MenusForRole::tableName(), ['menuId' => $menuId])
                ->execute();

            $mfrs = MenusForRole::findAll(['menuId' => $menu->parentId]);
            foreach ($mfrs as $mfr) {
                $model = new MenusForRole(['roleName' => $mfr->roleName, 'menuId' => $menuId]);
                if (!$model->save()) {
                    $transaction->rollBack();
                    return false;
                }
            }
            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw new $transaction;
        }
    }
}
