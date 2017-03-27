<?php

namespace yiicms\models\core;

use yii\db\ActiveRecord;
use yii\db\Query;
use yiicms\components\core\ArrayHelper;
use yiicms\components\core\behavior\JsonArrayBehavior;
use yiicms\components\core\behavior\MultiLangBehavior2;
use yiicms\components\core\blocks\BlockWidget;
use yiicms\components\core\RbacHelper;
use yiicms\components\core\validators\HtmlFilter;
use yiicms\components\core\yii\CommonApplicationTrait;
use yiicms\components\core\yii\Theme;

/**
 * This is the model class for table "web.blocks".
 * @property string $blockId Машинное имя блока
 * @property string $description Описание блока
 * @property string[] $titleM Заголовоки блока в виде массива на разных языках
 * @property string $title Заголовок блока на языке $lang
 * @property string $position Позиция блока на странице
 * @property integer $weight Вес блока, применяется при сортировке при расположении в одном блоке
 * @property bool $activy Флаг активности блока
 * @property array $params
 * @property string $contentClass Имя класса объекта который содержится в блоке
 * @property integer $pathInfoVisibleOrder порядок применения прав видимости блоков на страницах. Может принимать следующие значения:
 * self::$VISIBLE_IGNORE не учитывать настройки видимости
 * self::$VISIBLE_DENY_ALLOW запретить везде потом разрешить где указано
 * self::$VISIBLE_ALLOW_DENY разрешить везде потом запретить где указано
 * @property string $viewFile файл шаблона
 * @property string trgmIndex поисковый атрибут
 * @property string[] $visibleForRole список ролей для которых виден этот блок. Доступен только для чтения
 * @property BlocksForRole $blocksForRole список ролей к которым виден блок
 * @property string $lang с каким языком по умолчанию из языкового массива на котором представлен объект должен работать объект
 * @method attributeRulesLang() @see MultiLangBehavior2::attributeRulesLang()
 * @method attributeLabelsLang() @see MultiLangBehavior2::attributeLabelsLang()
 * @method string renderMultilang($activeForm, $attribute) @see MultiLangBehavior2::renderMultilang()
 */
class Blocks extends ActiveRecord
{
    const SC_INSERT = 'insert';
    const SC_EDIT = 'edit';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%blocks}}';
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
                        \Yii::t('yiicms', 'Заголовок блока')
                    ]
                ],
            ],
            [
                'class' => JsonArrayBehavior::class,
                'attributes' => ['params'],
            ],
        ]);
    }

    public function scenarios()
    {
        return [
            self::SCENARIO_DEFAULT => [],
            self::SC_EDIT => array_merge(array_keys($this->attributeLabelsLang()), [
                'title',
                'description',
                'position',
                'weight',
                'contentClass',
                'pathInfoVisibleOrder',
                'viewFile'
            ]),
        ];
    }

    public function init()
    {
        parent::init();
        if ($this->pathInfoVisibleOrder === null) {
            $this->pathInfoVisibleOrder = VisibleForPathInfo::VISIBLE_IGNORE;
        }
        if ($this->weight === null) {
            $this->weight = 0;
        }
        if ($this->activy === null) {
            $this->activy = 1;
        }
        if (empty($this->params)) {
            $this->params = [];
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge($this->attributeRulesLang(), [
            [['blockId', 'title', 'position', 'contentClass', 'viewFile'], 'required'],
            [['title'], 'string', 'max' => 255],
            [['weight', 'activy', 'pathInfoVisibleOrder'], 'integer'],
            [['active'], 'in', 'range' => [0, 1]],
            [['pathInfoVisibleOrder'], 'in', 'range' => VisibleForPathInfo::$visibleArray],
            [['position', 'contentClass', 'viewFile'], 'string', 'max' => 255],
            [['description'], 'string', 'max' => 1000],
            [['position'], 'string', 'max' => 255],
            [['position'], 'in', 'range' => Blocks::availablePosition()],
            [['title', 'description', 'position'], HtmlFilter::class],
            [['contentClass'], 'in', 'range' => self::getAvailableBlocksClass()],
            [['titleM', 'trgmIndex'], 'safe'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge($this->attributeLabelsLang(), [
            'blockId' => \Yii::t('yiicms', 'Идентификатор блока'),
            'description' => \Yii::t('yiicms', 'Описание блока'),
            'titleM' => \Yii::t('yiicms', 'Заголовок блока'),
            'title' => \Yii::t('yiicms', 'Заголовок блока'),
            'position' => \Yii::t('yiicms', 'Позиция блока на странице'),
            'weight' => \Yii::t('yiicms', 'Вес блока'),
            'activy' => \Yii::t('yiicms', 'Активный блок'),
            'params' => \Yii::t('yiicms', 'Список параметров в формате json для передачи в объект содержащийся в блоке'),
            'contentClass' => \Yii::t('yiicms', 'Содержимое блока'),
            'pathInfoVisibleOrder' => \Yii::t('yiicms', 'Порядок применения прав видимости на страницах'),
            'viewFile' => \Yii::t('yiicms', 'Файл шаблона'),
        ]);
    }

    /**
     * список доступных позийий для блоков в текущей теме оформления
     * @return \string[]
     */
    public static function availablePosition()
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
    public static function getAvailableBlocksClass()
    {
        $blocks = [];
        /** @var CommonApplicationTrait $app */
        $app = \Yii::$app;
        $namespaces = ArrayHelper::asArray($app->blocksNamespaces);

        foreach ($namespaces as $namespace) {
            $path = \Yii::getAlias(str_replace('\\', '/', "@$namespace"));
            $files = scandir($path);
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

    /**
     * выдает блоки в указанной позиции
     * @param string $position для какой позиции
     * @param int $userId идентфикатор пользователя для которого надо выдать блоки
     * @return Blocks[]
     */
    public static function forPosition($position, $userId)
    {
        if (self::$_blockInPosition === null) {
            self::loadBlocksForPage($userId);
        }
        return isset(self::$_blockInPosition[$position]) ? self::$_blockInPosition[$position] : [];
    }

    private static $_blockInPosition;

    /**
     * выполняет загрузку блоков доступных пользователю из базы
     * @param int $userId
     */
    private static function loadBlocksForPage($userId)
    {
        /** @var Blocks[] $blocks */
        $blocks = Blocks::find()
            ->innerJoinWith('blocksForRole')
            ->where(['activy' => 1])
            ->andWhere(['in', 'roleName', ArrayHelper::getColumn(RbacHelper::rolesRecursiveForUser($userId), 'name')])
            ->orderBy('weight')
            ->indexBy('blockId')
            ->all();

        BlocksVisibleForPathInfo::clearObjectsForThisPage($blocks);

        foreach ($blocks as $block) {
            self::$_blockInPosition[$block->position][] = $block;
        }
    }

    /**
     * предоставляет роли видимость блока
     * @param string $roleName имя роли
     * @return bool
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function grant($roleName)
    {
        return BlocksForRole::grant($this->blockId, $roleName);
    }

    /**
     * отменяет у роли видимость блока
     * @param string $roleName имя роли
     * @return bool
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function revoke($roleName)
    {
        return BlocksForRole::revoke($this->blockId, $roleName);
    }

    // ------------------------------------------------------ связи -------------------------------------------------------------

    public function getBlocksForRole()
    {
        return $this->hasMany(BlocksForRole::class, ['blockId' => 'blockId']);
    }

    // ----------------------------------------------- геттеры и сеттеры --------------------------------------------------------

    /**
     * выдает список ролей которым доступен для просмотра указанный блок
     * @return string[]
     */
    public function getVisibleForRole()
    {
        return (new Query())
            ->select(['roleName'])
            ->from(BlocksForRole::tableName())
            ->where(['blockId' => $this->blockId])
            ->column();
    }
}
