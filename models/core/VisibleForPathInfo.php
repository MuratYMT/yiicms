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
use yiicms\models\core\constants\VisibleForPathInfoConst;

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
    public function rules()
    {
        $primaryKeyArray = static::primaryKey();
        return [
            [['rule', 'template'], 'required'],
            [[reset($primaryKeyArray)], 'integer'],
            [['rule'], 'in', 'range' => VisibleForPathInfoConst::RULES_ARRAY],
            [['rule'], 'string', 'max' => 20],
            [['template'], 'string', 'max' => 255],
        ];
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
}
