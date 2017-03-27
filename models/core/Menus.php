<?php

namespace yiicms\models\core;

use yii\base\InvalidParamException;
use yii\db\ActiveRecord;
use yiicms\components\core\ArrayHelper;
use yiicms\components\core\behavior\MultiLangBehavior2;
use yiicms\components\core\Helper;
use yiicms\components\core\RbacHelper;
use yiicms\components\core\TreeHelper;
use yiicms\components\core\TreeTrait;
use yiicms\components\core\validators\HtmlFilter;

/**
 * This is the model class for table "web.menus".
 * @property integer $menuId идентификатор пункта меню
 * @property string $link ссылка
 * @property string $icon иконка FontAwesome
 * @property integer $weight вес пункта меню
 * @property integer $pathInfoVisibleOrder порядок применения прав видимости пунктов меню на страницах. Может принимать следующие значения:
 * self::$VISIBLE_IGNORE не учитывать настройки видимости
 * self::$VISIBLE_DENY_ALLOW запретить везде потом разрешить где указано
 * self::$VISIBLE_ALLOW_DENY разрешить везде потом запретить где указано
 * @property string $title заголовок меню
 * @property array $titleM массив заголовков на разных языках
 * @property string $subTitle массив подзоголовок меню
 * @property array $subTitleM массив подзаголовков на разных языках
 * @property MenusForRole[] $menusForRole список ролей к которым виден пункт меню
 * @property string $lang с каким языком по умолчанию из языкового массива на котором представлен объект должен работать объект
 * @method attributeRulesLang() @see MultiLangBehavior2::attributeRulesLang()
 * @method attributeLabelsLang() @see MultiLangBehavior2::attributeLabelsLang()
 * @method string renderMultilang($activeForm, $attribute) @see MultiLangBehavior2::renderMultilang()
 */
class Menus extends ActiveRecord
{
    use TreeTrait;

    const SC_EDIT = 'edit';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%menus}}';
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            [
                'class' => MultiLangBehavior2::class,
                'attributes' => [
                    'title' => [
                        [
                            ['string', 'max' => 255],
                            [HtmlFilter::class]
                        ],
                        \Yii::t('yiicms', 'Заголовок')
                    ],
                    'subTitle' => [
                        [
                            ['string', 'max' => 255],
                            [HtmlFilter::class]
                        ],
                        \Yii::t('yiicms', 'Подзаголовок')
                    ],
                ],
                'trgmIndex' => false,
            ],
        ]);
    }

    public function scenarios()
    {
        return [
            self::SCENARIO_DEFAULT => [],
            self::SC_EDIT => array_merge(
                array_keys($this->attributeLabelsLang()),
                ['lang', 'weight', 'link', 'pathInfoVisibleOrder', 'parentId', 'icon', '!mPath'])
        ];
    }

    public function isTransactional($operation)
    {
        return true;
    }

    public function init()
    {
        parent::init();
        if ($this->pathInfoVisibleOrder === null) {
            $this->pathInfoVisibleOrder = VisibleForPathInfo::VISIBLE_IGNORE;
        }
        if (empty($this->weight)) {
            $this->weight = 0;
        }

        if (empty($this->titleM)) {
            $this->titleM = [];
        }

        if (empty($this->subTitleM)) {
            $this->subTitleM = [];
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge($this->attributeRulesLang(), [
            [['parentId'], 'default', 'value' => 0],
            [['mPath'], 'default', 'value' => ''],
            [['title'], 'required'],
            [['parentId', 'weight', 'pathInfoVisibleOrder'], 'integer'],
            [['pathInfoVisibleOrder'], 'in', 'range' => [-1, 0, 1]],
            [['title', 'subTitle'], 'string', 'max' => 255],
            [['mPath'], 'string', 'max' => 1000],
            [['link'], 'string', 'max' => 2000],
            [['title', 'subTitle', 'link'], HtmlFilter::class],
            [
                ['parentId'],
                function ($attribute) {
                    if (!$this->hasErrors()) {
                        if ((int)$this->parentId === 0) {
                            return;
                        }
                        $parent = $this->parent;
                        if ($parent === null) {
                            $this->addError($attribute, \Yii::t('yiicms', 'Неизвестное родительское меню'));
                            return;
                        }
                        if (!$this->isNewRecord && TreeHelper::detectLoop($parent->mPath, $this->mPath)) {
                            $this->addError('parentId', \Yii::t('yiicms', 'Невозможно установить родительское меню. Обнаружена циклическая ссылка'));
                        }
                    }
                },
            ],
            [['icon'], 'string', 'max' => 64],
            [['icon'], HtmlFilter::class],
            [['titleM', 'subTitleM'], 'safe'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge($this->attributeLabelsLang(), [
            'menuId' => \Yii::t('yiicms', 'Идентификатор'),
            'levelNod' => \Yii::t('yiicms', 'Уровень узла'),
            'parentId' => \Yii::t('yiicms', 'Идентификатор родительского узла'),
            'mPath' => \Yii::t('yiicms', 'Материализованный путь'),
            'link' => \Yii::t('yiicms', 'Ссылка'),
            'weight' => \Yii::t('yiicms', 'Вес'),
            'pathInfoVisibleOrder' => \Yii::t('yiicms', 'Порядок применения прав видимости'),
            'title' => \Yii::t('yiicms', 'Заголовок'),
            'titleM' => \Yii::t('yiicms', 'Заголовок'),
            'subTitle' => \Yii::t('yiicms', 'Подзаголовок'),
            'subTitleM' => \Yii::t('yiicms', 'Подзаголовок'),
            'lang' => \Yii::t('yiicms', 'Язык'),
            'icon' => \Yii::t('yiicms', 'Иконка'),
        ]);
    }

    /**
     * предоставляет роли видимость пункта меню
     * @param string $roleName имя роли
     * @param bool $recursive предоставлять ли дочерним пунктам меню
     * @return bool
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function grant($roleName, $recursive = false)
    {
        $trans = self::getDb()->beginTransaction();
        try {
            if (!MenusForRole::grant($this, $roleName)) {
                $trans->rollBack();
                return false;
            }

            if ($recursive) {
                $childsList = $this->childrenBranch;
                foreach ($childsList as $child) {
                    if (!$child->grant($roleName, $recursive)) {
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
     * @param string $roleName имя роли
     * @param bool $recursive отменять ли к дочерним пунктам меню
     * @return bool
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function revoke($roleName, $recursive = false)
    {
        $trans = self::getDb()->beginTransaction();
        try {
            if (!MenusForRole::revoke($this, $roleName)) {
                $trans->rollBack();
                return false;
            }
            if ($recursive) {
                $childsList = $this->childrenBranch;
                foreach ($childsList as $child) {
                    if (!$child->revoke($roleName, $recursive)) {
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
    public static function allMenus()
    {
        $menusRaw = self::find()
            ->distinct()
            ->orderBy(['weight' => SORT_ASC, 'menuId' => SORT_ASC])
            ->asArray()
            ->all(self::getDb());

        $menusRaw = TreeHelper::build($menusRaw, 'menuId', 'weight');

        return Helper::populateArray(Menus::class, $menusRaw);
    }

    /**
     * загружает пункты меню из указанного блока доступные для текущего пользователя. корневой пункт в результат не включается
     * @param $userId integer Для какого пользователя строится меню
     * @param $rootMenuId int корневой пункт меню пункты которого надо выдать
     * @return Menus[]
     * @throws InvalidParamException
     */
    public static function branchForUser($userId, $rootMenuId)
    {
        $roles = ArrayHelper::getColumn(RbacHelper::rolesRecursiveForUser($userId), 'name');
        /** @var Menus $rootMenu */
        $rootMenu = self::find()
            ->innerJoinWith('menusForRole')
            ->where([self::tableName() . '.[[menuId]]' => $rootMenuId])
            ->andWhere([MenusForRole::tableName() . '.[[roleName]]' => $roles])
            ->one(self::getDb());
        if (null === $rootMenu) {
            return [];
        }

        $menusRaw = self::find()->distinct()
            ->innerJoinWith('menusForRole')
            ->where(['like', 'mPath', $rootMenu->mPath . '%', false])
            ->andWhere([MenusForRole::tableName() . '.[[roleName]]' => $roles])
            ->andWhere(['<>', self::tableName() . '.[[menuId]]', $rootMenuId])
            ->asArray()
            ->indexBy('menuId')
            ->all(self::getDb());
        MenusVisibleForPathInfo::clearObjectsForThisPage($menusRaw);
        $menusRaw = TreeHelper::build($menusRaw, 'menuId', 'weight');

        return Helper::populateArray(Menus::class, $menusRaw);
    }

    public function beforeSave($insert)
    {
        if (!$insert) {
            TreeHelper::updateHierarchicalData($this);
        }
        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes)
    {
        //устанавливаем видимость для пункта меню как у родительского
        if ($insert) {
            TreeHelper::setMPath($this);
            MenusForRole::asParent($this);
        }
        parent::afterSave($insert, $changedAttributes);
    }

    public function beforeDelete()
    {
        //перемещаем дочерние категории в родительскую категорию
        $parentId = $this->parentId;
        foreach ($this->children as $menu) {
            $menu->parentId = $parentId;
            if (false === $menu->save()) {
                return false;
            }
        }
        unset($this->children);
        return parent::beforeDelete();
    }

    /**
     * устанавливает в дочерней ветке видимость для ролей как у текущего элемента
     * @return bool
     */
    public function replaceChildrenVisibleForRole()
    {
        $trans = self::getDb()->beginTransaction();
        try {
            foreach ($this->children as $menu) {
                if (!MenusForRole::asParent($menu) || !$menu->replaceChildrenVisibleForRole()) {
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

    // ------------------------------------ связи ---------------------------------------------

    public function getMenusForRole()
    {
        return $this->hasMany(MenusForRole::class, ['menuId' => 'menuId']);
    }

    // -------------------------------- геттеры и сеттеры ---------------------------------------------
}
