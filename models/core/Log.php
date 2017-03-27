<?php

namespace yiicms\models\core;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "web.log".
 * @property integer $id
 * @property integer $level
 * @property string $category
 * @property double $log_time
 * @property string $prefix
 * @property string $message
 */
class Log extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%log}}';
    }

    public function scenarios()
    {
        return [
            self::SCENARIO_DEFAULT => [],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['level'], 'integer'],
            [['log_time'], 'number'],
            [['prefix', 'message'], 'string'],
            [['category'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('yiicms', 'ID'),
            'level' => Yii::t('yiicms', 'Уровень'),
            'category' => Yii::t('yiicms', 'Категория'),
            'log_time' => Yii::t('yiicms', 'Время'),
            'prefix' => Yii::t('yiicms', 'Prefix'),
            'message' => Yii::t('yiicms', 'Текст ошибки'),
        ];
    }
}
