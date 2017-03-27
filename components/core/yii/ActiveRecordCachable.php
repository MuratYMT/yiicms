<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 06.02.2016
 * Time: 10:58
 */

namespace yiicms\components\core\yii;

use yii\caching\TagDependency;
use yii\db\ActiveRecord;

class ActiveRecordCachable extends ActiveRecord
{
    /**
     * теги зависимости
     * @return array
     */
    protected static function getDependencyTags()
    {
        return [static::tableName()];
    }

    /**
     * @inheritdoc
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        $result = parent::save($runValidation, $attributeNames);
        if ($result) {
            TagDependency::invalidate(\Yii::$app->cache, static::getDependencyTags());
        }
        return $result;
    }

    public function delete()
    {
        $result = parent::delete();
        if ($result) {
            TagDependency::invalidate(\Yii::$app->cache, static::getDependencyTags());
        }
        return $result;
    }
}
