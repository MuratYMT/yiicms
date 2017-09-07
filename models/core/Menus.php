<?php

namespace yiicms\models\core;

use yii\db\ActiveRecord;
use yiicms\components\core\behavior\MultiLangBehavior2;
use yiicms\components\core\TreeHelper;
use yiicms\components\core\TreeTrait;
use yiicms\components\core\validators\HtmlFilter;
use yiicms\models\core\constants\VisibleForPathInfoConst;

/**
 * This is the model class for table "web.menus".
 * @property integer $menuId идентификатор пункта меню
 * @property string $link ссылка
 * @property string $icon иконка FontAwesome
 * @property integer $weight вес пункта меню
 * @property integer $pathInfoVisibleOrder порядок применения прав видимости пунктов меню на страницах.
 * Может принимать следующие значения:
 * self::$VISIBLE_IGNORE не учитывать настройки видимости
 * self::$VISIBLE_DENY_ALLOW запретить везде потом разрешить где указано
 * self::$VISIBLE_ALLOW_DENY разрешить везде потом запретить где указано
 * @property string $title заголовок меню
 * @property array $titleM массив заголовков на разных языках
 * @property string $subTitle массив подзоголовок меню
 * @property array $subTitleM массив подзаголовков на разных языках
 * @property MenusForRole[] $menusForRole список ролей к которым виден пункт меню
 * @property string $lang с каким языком по умолчанию из языкового массива на котором представлен
 * объект должен работать объект
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
            $this->pathInfoVisibleOrder = VisibleForPathInfoConst::VISIBLE_IGNORE;
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
                            $this->addError(
                                'parentId',
                                \Yii::t(
                                    'yiicms',
                                    'Невозможно установить родительское меню. Обнаружена циклическая ссылка'
                                )
                            );
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

    // ---------------------------------------------- связи -----------------------------------------------------------

    public function getMenusForRole()
    {
        return $this->hasMany(MenusForRole::class, ['menuId' => 'menuId']);
    }

    // --------------------------------------- геттеры и сеттеры ------------------------------------------------------
}
