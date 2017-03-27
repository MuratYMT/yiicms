<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 06.04.2016
 * Time: 14:37
 */

namespace yiicms\components\core;

class ArrayHelper extends \yii\helpers\ArrayHelper
{
    /**
     * удаляет из переданного массива элементы имеющие значение пераданные в $value
     * @param array $array массив
     * @param int|string|array $values значения элементов которые надо удаляить
     * @param bool $strict Если третий параметр strict установлен в TRUE, то функция будет искать идентичные элементы в array.
     * Это означает, что также будут проверяться типы values в array
     */
    public static function removeValues(&$array, $values, $strict = true)
    {
        foreach ((array)$values as $value) {
            if (false !== ($key = array_search($value, $array, $strict))) {
                unset($array[$key]);
            }
        }
    }

    /**
     * определяет какие элементы были удалены или добавлены в $array1 по сравнению с $array2
     * @param array $array1 проверяемый массив
     * @param array $array2 эталонный массив
     * @return array массив [added[], removed[]]
     */
    public static function diffValues($array1, $array2)
    {
        $added = array_diff($array1, $array2);
        $removed = array_diff($array2, $array1);
        return [$added, $removed];
    }

    /**
     * функция проверяет $value если не массив то делает $value элементом массива
     * @param mixed $value
     * @return mixed[]
     */
    public static function asArray($value)
    {
        /** @noinspection ArrayCastingEquivalentInspection */
        return is_array($value) ? $value : [$value];
    }
}
