<?php

namespace yiicms\models\core;

/**
 * This is the model class for table "web.blocksForPathIno".
 * @property integer $permId ID правила
 * @property integer $blockId ID блока для которого применяется это правило
 * @property string $rule правило обработки шаблона
 * contain - содержит
 * begins - начинается с
 * ends - заканчивается
 * equal - равен
 * pcre - значение шаблон PCRE
 * @property Blocks $block
 * @property string $template шаблон pathInfo
 */
class BlocksVisibleForPathInfo extends VisibleForPathInfo
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%blocksForPathInfo}}';
    }

    protected static function objectKey()
    {
        return 'blockId';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(
            parent::rules(),
            [
                [['blockId'], 'exist', 'targetClass' => Blocks::class],
            ]
        );
    }

    // -------------------------------------------------------- связи ---------------------------------------------------

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBlock()
    {
        return $this->hasOne(Blocks::class, ['blockId' => 'blockId']);
    }
}
