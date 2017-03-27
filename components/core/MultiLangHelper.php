<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 17.11.2015
 * Time: 8:43
 */

namespace yiicms\components\core;

use codemix\localeurls\UrlManager;

/**
 * Class MultiLangHelper хэлпер для работы с языковыми массивами
 * @package yiicms\components\core
 */
class MultiLangHelper
{
    /**
     * удаляет значение в языковом массиве
     * @param string|array $langArray
     * @param $lang 2х буквенное обозночение языка
     * @return string
     */
    public static function delValue($langArray, $lang)
    {
        if ($langArray === null || (is_string($langArray) && $langArray === '')) {
            $langArray = [];
        }

        $arrayValue = self::decodeLangArray($langArray);
        if (isset($arrayValue[$lang])) {
            unset($arrayValue[$lang]);
        }

        return self::encodeLangArray($arrayValue);
    }

    /**
     * устнавливает значение в языковом массиве
     * @param string|array $langArray языковой массив
     * @param string|null $value значение если null или '' то значение для языка удаляется
     * @param string $lang 2х буквенное обозночение языка
     * @return string
     */
    public static function setValue($langArray, $value, $lang)
    {
        if (empty($langArray)) {
            $langArray = [];
        }

        $arrayValue = self::decodeLangArray($langArray);

        if ($value === '') {
            $value = null;
        }

        if ($value === null && isset($arrayValue[$lang])) {
            unset($arrayValue[$lang]);
        } else {
            $arrayValue[$lang] = $value;
        }

        return self::encodeLangArray($arrayValue);
    }

    /**
     * выдает из языкового массива $var запись на языке пользователя
     * если записи на таком языке нету то выдается запись на языке по умолчанию
     * @param string|array $langArray языковой массив
     * @param string $lang 2х буквенное обозночение языка на котором должна быть возвращаемое значение
     * @return string|null null если в переданном значении $var нет записей
     */
    public static function getValue($langArray, $lang = null)
    {
        if (empty($langArray)) {
            return null;
        }

        $arrayValue = self::decodeLangArray($langArray);

        //массив языков пустой
        if (empty($arrayValue)) {
            return null;
        }

        if ($lang === null) {
            $lang = \Yii::$app->language;
        }

        //значение с указанным языком существует
        if (isset($arrayValue[$lang])) {
            return $arrayValue[$lang];
        }

        //требуемого языка нет берем язык сайта по умолчанию

        /** @var UrlManager $urlManager */
        $urlManager = \Yii::$app->urlManager;
        $lang = $urlManager->getDefaultLanguage();

        if (isset($arrayValue[$lang])) {
            return $arrayValue[$lang];
        }

        //если не один из предпочитаемых языков не найден выбираем первый попавшийся
        return reset($arrayValue);
    }

    /**
     * выполняет преобразование массива PHP в формат хранения языкового массива
     * @param array $langArray
     * @return string
     */
    public static function encodeLangArray(array $langArray)
    {
        return json_encode($langArray, JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * выполняет преобразование языкового массива из одного из форматов хранения в массив PHP
     * @param string|array $langArray
     * @return array
     */
    public static function decodeLangArray($langArray)
    {
        if (empty($langArray)) {
            return [];
        }

        if (is_array($langArray)) {
            $arrayValue = $langArray;
        } elseif (strpos($langArray, 'a:') === 0) {
            //сериализованный массив php @deprecated
            try {
                $arrayValue = @unserialize($langArray);
            } catch (\Exception $e) {
                $arrayValue = [];
            }
        } elseif ($langArray[0] === '{' && strpos($langArray, '<->') !== false) {
            //postgres массив @deprecated
            $titles = self::pgArrayParse($langArray);
            $arrayValue = [];
            if ($titles !== null) {
                foreach ($titles as $row) {
                    $exp = explode('<->', $row, 2);
                    if (count($exp) === 2) {
                        list($lng, $title) = $exp;
                        $arrayValue[$lng] = $title;
                    }
                }
            }
        } else {
            //массив в json
            try {
                $arrayValue = json_decode($langArray, true);
            } catch (\Exception $e) {
                $arrayValue = [];
            }
        }
        return $arrayValue;
    }

    /**
     * преобразует строку в формате pgsql в массив
     * @param string $dbarr
     * @return array
     */
    private static function pgArrayParse($dbarr)
    {
        if ($dbarr === null) {
            return null;
        }
        // Take off the first and last characters (the braces)
        $arr = substr($dbarr, 1, strlen($dbarr) - 2);

        // Pick out array entries by carefully parsing.  This is necessary in order
        // to cope with double quotes and commas, etc.
        $elements = [];
        $i = $j = 0;
        $in_quotes = false;
        while ($i < strlen($arr)) {
            // If current char is a double quote and it's not escaped, then
            // enter quoted bit
            $char = substr($arr, $i, 1);
            if ($char === '"' && ($i === 0 || substr($arr, $i - 1, 1) !== '\\')) {
                $in_quotes = !$in_quotes;
            } elseif ($char === ',' && !$in_quotes) {
                // Add text so far to the array
                $elements[] = substr($arr, $j, $i - $j);
                $j = $i + 1;
            }
            $i++;
        }
        // Add final text to the array
        $elements[] = substr($arr, $j);

        // Do one further loop over the elements array to remote double quoting
        // and escaping of double quotes and backslashes
        $size = count($elements);
        for ($i = 0; $i < $size; $i++) {
            $v = $elements[$i];
            if (mb_strpos($v, '"') === 0) {
                $v = substr($v, 1, strlen($v) - 2);
                $v = str_replace('\\"', '"', $v);
                $v = str_replace('\\\\', '\\', $v);
                $elements[$i] = $v;
            }
        }

        return $elements;
    }
}
