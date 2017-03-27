<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 18.01.2016
 * Time: 12:02
 */

namespace yiicms\models\core;

use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;

/**
 * Class VisibleForPathInfo
 * @package yiicms\models\web
 * @property integer $permId ID правила
 * @property string $rule правило обработки шаблона
 * contain - содержит
 * begins - начинается с
 * ends - заканчивается
 * equal - равен
 * pcre - значение шаблон PCRE
 * @property string $template шаблон pathInfo
 */
abstract class VisibleForPathInfo extends ActiveRecord
{
    /**
     * запретить везде потом разрешить где указанно
     */
    const VISIBLE_DENY_ALLOW = -1;
    /**
     * разрешить везде потом запретить где указано
     */
    const VISIBLE_ALLOW_DENY = 1;
    /**
     * не учитывать настройки видимости
     */
    const VISIBLE_IGNORE = 0;
    /**
     * правило обработки шаблона pathInfo содержит temlate
     */
    const RULE_CONTAIN = 'contain';
    /**
     * правило обработки шаблона pathInfo начинается с temlate
     */
    const RULE_BEGIN = 'begins';
    /**
     * правило обработки шаблона pathInfo заканчивается на temlate
     */
    const RULE_END = 'ends';
    /**
     * правило обработки шаблона pathInfo = temlate
     */
    const RULE_EQUAL = 'equal';
    /**
     * правило обработки шаблона temlate в pathInfo с использованием PCRE
     */
    const RULE_PCRE = 'pcre';

    /**
     * массив допустимых правил
     * @var array
     */
    public static $rulesArray = [
        self::RULE_CONTAIN,
        self::RULE_BEGIN,
        self::RULE_END,
        self::RULE_EQUAL,
        self::RULE_PCRE,
    ];

    /**
     * массив допустимых типов видимости
     * @var array
     */
    public static $visibleArray = [self::VISIBLE_ALLOW_DENY, self::VISIBLE_IGNORE, self::VISIBLE_DENY_ALLOW];

    public function rules()
    {
        $primaryKeyArray = static::primaryKey();
        return [
            [['rule', 'template'], 'required'],
            [[reset($primaryKeyArray)], 'integer'],
            [['rule'], 'in', 'range' => self::$rulesArray],
            [['rule'], 'string', 'max' => 20],
            [['template'], 'string', 'max' => 255],
        ];
    }

    public static function visibleOrderLabels($visible = null)
    {
        $labels = [
            self::VISIBLE_ALLOW_DENY => \Yii::t('yiicms', 'Виден где не запрещено (Запрещено только то что явно запрещено)'),
            self::VISIBLE_IGNORE => \Yii::t('yiicms', 'Не учитывать'),
            self::VISIBLE_DENY_ALLOW => \Yii::t('yiicms', 'Виден где разрешено (Запрещено все кроме явно разрешенного)'),
        ];

        if ($visible === null) {
            return $labels;
        } elseif (isset($labels[$visible])) {
            return $labels[$visible];
        }
        return null;
    }

    public static function ruleLabels($rule = null)
    {
        $labels = [
            self::RULE_CONTAIN => \Yii::t('yiicms', 'Содержит'),
            self::RULE_BEGIN => \Yii::t('yiicms', 'Начинается с'),
            self::RULE_END => \Yii::t('yiicms', 'Заканчивается на'),
            self::RULE_EQUAL => \Yii::t('yiicms', 'Равно'),
            self::RULE_PCRE => \Yii::t('yiicms', 'Соответствует регулярному выражению'),
        ];

        if ($rule === null) {
            return $labels;
        } elseif (isset($labels[$rule])) {
            return $labels[$rule];
        }
        return null;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'permId' => \Yii::t('yiicms', 'Perm ID'),
            'rule' => \Yii::t('yiicms', 'Как обрабатывать поле template'),
            'template' => \Yii::t('yiicms', 'Шаблон URN при котором действует правило'),
        ];
    }

    /**
     * поле отвечающее за id объекта(меню или блока)
     * @return string
     */
    protected static function objectKey()
    {
        return '';
    }

    /**
     * функция удаляет пункты меню/блоки которые не должны быть видны на этой странице
     * @param array $dataRaw сырые строки из таблицы базы данных из которых надо удалить невидимые объекты
     * обязательное условия: 1. в качестве ключей массива должны использоваться идентификаторы объектов
     * 2. должна быть колонка 'pathInfoVisibleOrder' определяющая порядок прменения правил
     * @throws InvalidConfigException
     */
    public static function clearObjectsForThisPage(&$dataRaw)
    {
        $key = static::objectKey();
        //загружаем таблицу видимости пунктов меню в зависимости от URN
        /** @var MenusVisibleForPathInfo[] $rows */
        $rows = static::find()->all();

        //создаем массив блоков для минимизации обработки в последующих этапах
        $templates = [];
        foreach ($rows as $row) {
            $templates[$row->$key][] = $row;
        }

        $pathInfo = \Yii::$app->request->pathInfo;
        //удаляем объекты которые не видны на этой транице
        foreach ($dataRaw as $itemId => $row) {
            switch ($row['pathInfoVisibleOrder']) {
                case self::VISIBLE_DENY_ALLOW:
                    //сперва запретить везде, потом разрешить
                    if (!isset($templates[$itemId]) ||        //правила не определены значит невидно нигде
                        //разрешающих правил для этого объекта нет
                        (isset($templates[$itemId]) && !self::testTemplates($templates[$itemId], $pathInfo))
                    ) {
                        //разрешения нет
                        unset($dataRaw[$itemId]);
                    }
                    break;
                case self::VISIBLE_ALLOW_DENY:
                    //сперва разрешить везде, потом запретить
                    if (isset($templates[$itemId]) && self::testTemplates($templates[$itemId], $pathInfo)) { //есть запрещающее правило
                        //запрещение есть запрещаем
                        unset($dataRaw[$itemId]);
                    }
                    break;
            }
        }
    }

    /**
     * проверяет видим ли пункт меню на этой странице
     * @param  VisibleForPathInfo[] $templates массив шаблонов
     * @param string $pathInfo URN текущей страницы
     * @return bool
     */
    private static function testTemplates($templates, $pathInfo)
    {
        foreach ($templates as $template) {
            //для каждого правила обработки шаблона вызываем свою функцию поиска совпадений
            switch ($template->rule) {
                case self::RULE_CONTAIN:
                    $found = mb_strpos($pathInfo, $template->template) !== false;
                    break;
                case self::RULE_BEGIN:
                    $found = mb_strpos($pathInfo, $template->template) === 0;
                    break;
                case self::RULE_END:
                    $found = mb_strpos(strrev($pathInfo), strrev($template->template)) === 0;
                    break;
                case self::RULE_EQUAL:
                    $found = $pathInfo === $template->template;
                    break;
                case self::RULE_PCRE:
                    $found = preg_match('/' . $template->template . '/', $pathInfo) ? true : false;
                    break;
                default:
                    $found = false;
            }
            if ($found) {
                return true;
            }
        }

        return false;
    }
}
