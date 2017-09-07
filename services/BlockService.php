<?php

namespace yiicms\services;

use yiicms\components\core\ArrayHelper;
use yiicms\components\core\blocks\BlockWidget;
use yiicms\components\core\RbacHelper;
use yiicms\components\core\yii\Theme;
use yiicms\components\YiiCms;
use yiicms\models\core\Blocks;
use yiicms\models\core\BlocksForRole;
use yiicms\models\core\BlocksVisibleForPathInfo;
use yiicms\models\core\constants\VisibleForPathInfoConst;
use yiicms\models\core\Settings;

/**
 * Created by PhpStorm.
 * User: murat
 * Date: 03.09.2017
 * Time: 19:06
 */
class BlockService
{
    use VisibleForPathTrait;

    private $labelsVisibleCache;

    public function visibleOrderLabels($visible = null)
    {
        if ($this->labelsVisibleCache === null) {
            $this->labelsVisibleCache = [
                VisibleForPathInfoConst::VISIBLE_ALLOW_DENY => \Yii::t(
                    'yiicms',
                    'Виден где не запрещено (Запрещено только то что явно запрещено)'
                ),
                VisibleForPathInfoConst::VISIBLE_IGNORE => \Yii::t('yiicms', 'Не учитывать'),
                VisibleForPathInfoConst::VISIBLE_DENY_ALLOW => \Yii::t(
                    'yiicms',
                    'Виден где разрешено (Запрещено все кроме явно разрешенного)'
                ),
            ];
        }

        if ($visible === null) {
            return $this->labelsVisibleCache;
        }
        if (isset($this->labelsVisibleCache[$visible])) {
            return $this->labelsVisibleCache[$visible];
        }
        return null;
    }

    private $labelsRuleCache;

    public function ruleLabels($rule = null)
    {
        if ($this->labelsRuleCache === null) {
            $this->labelsRuleCache = [
                VisibleForPathInfoConst::RULE_CONTAIN => \Yii::t('yiicms', 'Содержит'),
                VisibleForPathInfoConst::RULE_BEGIN => \Yii::t('yiicms', 'Начинается с'),
                VisibleForPathInfoConst::RULE_END => \Yii::t('yiicms', 'Заканчивается на'),
                VisibleForPathInfoConst::RULE_EQUAL => \Yii::t('yiicms', 'Равно'),
                VisibleForPathInfoConst::RULE_PCRE => \Yii::t('yiicms', 'Соответствует регулярному выражению'),
            ];
        }
        if ($rule === null) {
            return $this->labelsRuleCache;
        }
        if (isset($this->labelsRuleCache[$rule])) {
            return $this->labelsRuleCache[$rule];
        }
        return null;
    }

    /**
     * список доступных позийий для блоков в текущей теме оформления
     * @return \string[]
     */
    public function availablePosition()
    {
        /** @var Theme $theme */
        /** @noinspection OneTimeUseVariablesInspection */
        $theme = Settings::get('core.theme');
        return $theme::positions();
    }

    /**
     * список доступных классов блоков
     * @return string[]
     */
    public function getAvailableBlocksClass()
    {
        $blocks = [];
        $namespaces = ArrayHelper::asArray(YiiCms::$app->blocksNamespaces);

        foreach ($namespaces as $namespace) {
            $path = \Yii::getAlias(str_replace('\\', '/', "@$namespace"));
            $files = scandir($path, SCANDIR_SORT_ASCENDING);
            foreach ($files as $file) {
                if ($file === '..' || $file === '.') {
                    continue;
                }
                $f = $path . DIRECTORY_SEPARATOR . $file;
                if (is_file($f) || !file_exists($f . DIRECTORY_SEPARATOR . 'Widget.php')) {
                    continue;
                }

                $class = "$namespace\\$file\\Widget";
                if (is_subclass_of($class, BlockWidget::class)) {
                    $blocks[] = $class;
                }
            }
        }

        return $blocks;
    }

    private $blockInPositionCache;

    /**
     * выдает блоки в указанной позиции
     * @param string $position для какой позиции
     * @param int $userId идентфикатор пользователя для которого надо выдать блоки
     * @return Blocks[]
     */
    public function forPosition($position, $userId)
    {
        if ($this->blockInPositionCache === null) {
            $this->loadBlocksForPage($userId);
        }
        return isset($this->blockInPositionCache[$position]) ? $this->blockInPositionCache[$position] : [];
    }

    /**
     * выполняет загрузку блоков доступных пользователю из базы
     * @param int $userId
     */
    private function loadBlocksForPage($userId)
    {
        /** @var Blocks[] $blocks */
        $blocks = Blocks::find()
            ->innerJoinWith('blocksForRole')
            ->where(['activy' => 1])
            ->andWhere(['in', 'roleName', ArrayHelper::getColumn(RbacHelper::rolesRecursiveForUser($userId), 'name')])
            ->orderBy('weight')
            ->indexBy('blockId')
            ->all();

        $this->clearObjectsForThisPage(
            $blocks,
            BlocksVisibleForPathInfo::primaryKey(),
            BlocksVisibleForPathInfo::find()->all()
        );

        foreach ($blocks as $block) {
            $this->blockInPositionCache[$block->position][] = $block;
        }
    }

    /**
     * предоставляет роли видимость блока
     * @param Blocks $block
     * @param string $roleName имя роли
     * @return bool
     */
    public function grant(Blocks $block, $roleName)
    {
        $mfr = BlocksForRole::findOne(['roleName' => $roleName, 'blockId' => $block->blockId]);
        if ($mfr !== null) {
            return true;
        }
        $mfr = new BlocksForRole(['roleName' => $roleName, 'blockId' => $block->blockId]);
        return $mfr->save();
    }

    /**
     * отменяет у роли видимость блока
     * @param Blocks $block
     * @param string $roleName имя роли
     * @return bool
     */
    public function revoke(Blocks $block, $roleName)
    {
        $mfr = BlocksForRole::findOne(['roleName' => $roleName, 'blockId' => $block->blockId]);
        if ($mfr === null) {
            return true;
        }

        return false !== $mfr->delete();
    }
}
