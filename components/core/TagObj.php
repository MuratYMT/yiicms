<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 26.05.2016
 * Time: 15:54
 */

namespace yiicms\components\core;

use yiicms\models\content\Tag;
use yii\base\Object;

/**
 * Class TagObj транспортный класс
 * @package yiicms\components\core
 * @property \yiicms\models\content\Tag $contentTag
 */
class TagObj extends Object
{
    public $tagId;
    public $slug;
    public $title;

    public function asArray()
    {
        return ['tagId' => $this->tagId, 'slug' => $this->slug, 'title' => $this->title];
    }

    /**
     * создает объект TagObj  из json строки.
     * Если в json содержится двумерный массив то создается массив из объектов File
     * @param string $json
     * @return TagObj[]
     */
    public static function createFromJson($json)
    {
        /** @var array $array */
        $array = @json_decode($json, true);
        if (!is_array($array)) {
            return [];
        }
        $result = [];
        foreach ($array as $row) {
            $obj = new self($row);
            $result[$obj->tagId] = $obj;
        }
        return $result;
    }

    /**
     * преобразует файлы в json строку
     * @param TagObj|TagObj[] $value
     * @return string
     */
    public static function saveToJson($value)
    {
        $value = ArrayHelper::asArray($value);

        $result = [];
        foreach ($value as $tag) {
            /** @var TagObj $tag */
            $result[$tag->tagId] = $tag->asArray();
        }
        return json_encode($result, JSON_FORCE_OBJECT);
    }

    // ------------------------------------------------ геттеры и сеттеры ------------------------------------------------------------------

    public function getContentTag()
    {
        return Tag::findOne($this->tagId);
    }
}
