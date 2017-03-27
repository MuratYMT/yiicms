<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 07.07.2015
 * Time: 10:41
 */

namespace yiicms\components\core;

use yii\base\Model;
use yii\db\BaseActiveRecord;
use yiicms\components\core\widgets\Alert;

class Helper
{
    /**
     * Выводит алерты с ошибками модели
     * @param Model $model
     */
    public static function errorModel($model)
    {
        /** @var string[] $attributeErrors */
        foreach ($model->errors as $attributeErrors) {
            foreach ($attributeErrors as $error) {
                Alert::error($error);
            }
        }
    }

    /**
     * выдает короткое имя класса без namespace
     * @param string|Object $item
     * @return string
     */
    public static function classShortName($item)
    {
        if (is_string($item)) {
            $array = explode('\\', $item);
            return array_pop($array);
        }

        $reflector = new \ReflectionClass($item);
        return $reflector->getShortName();
    }

    private static $arStrES = ['ае', 'уе', 'ое', 'ые', 'ие', 'эе', 'яе', 'юе', 'ёе', 'ее', 'ье', 'ъе', 'ый', 'ий'];
    private static $arStrOS = ['аё', 'уё', 'оё', 'ыё', 'иё', 'эё', 'яё', 'юё', 'ёё', 'её', 'ьё', 'ъё', 'ый', 'ий'];
    private static $arStrRS = ['а$', 'у$', 'о$', 'ы$', 'и$', 'э$', 'я$', 'ю$', 'ё$', 'е$', 'ь$', 'ъ$', '@', '@'];
    private static $replace = [
        'А' => 'A',
        'а' => 'a',
        'Б' => 'B',
        'б' => 'b',
        'В' => 'V',
        'в' => 'v',
        'Г' => 'G',
        'г' => 'g',
        'Д' => 'D',
        'д' => 'd',
        'Е' => 'Ye',
        'е' => 'e',
        'Ё' => 'Ye',
        'ё' => 'e',
        'Ж' => 'Zh',
        'ж' => 'zh',
        'З' => 'Z',
        'з' => 'z',
        'И' => 'I',
        'и' => 'i',
        'Й' => 'Y',
        'й' => 'y',
        'К' => 'K',
        'к' => 'k',
        'Л' => 'L',
        'л' => 'l',
        'М' => 'M',
        'м' => 'm',
        'Н' => 'N',
        'н' => 'n',
        'О' => 'O',
        'о' => 'o',
        'П' => 'P',
        'п' => 'p',
        'Р' => 'R',
        'р' => 'r',
        'С' => 'S',
        'с' => 's',
        'Т' => 'T',
        'т' => 't',
        'У' => 'U',
        'у' => 'u',
        'Ф' => 'F',
        'ф' => 'f',
        'Х' => 'Kh',
        'х' => 'kh',
        'Ц' => 'Ts',
        'ц' => 'ts',
        'Ч' => 'Ch',
        'ч' => 'ch',
        'Ш' => 'Sh',
        'ш' => 'sh',
        'Щ' => 'Shch',
        'щ' => 'shch',
        'Ъ' => '',
        'ъ' => '',
        'Ы' => 'Y',
        'ы' => 'y',
        'Ь' => '',
        'ь' => '',
        'Э' => 'E',
        'э' => 'e',
        'Ю' => 'Yu',
        'ю' => 'yu',
        'Я' => 'Ya',
        'я' => 'ya',
        '@' => 'y',
        '$' => 'ye',
        ' ' => '-',
        ',' => '-',
    ];

    /**
     * функция транслитерации
     * @param $string
     * @return string
     */
    public static function translate($string)
    {
        $string = str_replace(self::$arStrES, self::$arStrRS, $string);
        /** @noinspection CascadeStringReplacementInspection */
        $string = str_replace(self::$arStrOS, self::$arStrRS, $string);

        return preg_replace('/[^a-zA-Z0-9\-]+/', '', iconv('UTF-8', 'UTF-8//IGNORE', strtr($string, self::$replace)));
    }

    public static function mergePath()
    {
        return rtrim(
            preg_replace(
                '/(?<!:)\/{2,}/',
                '/',
                str_replace(
                    '\\',
                    '/',
                    implode(
                        '/',
                        func_get_args()
                    )
                )
            ),
            '/'
        );
    }

    /**
     * удаляет $word с начала строки $str
     * @param string $str строка оригинал
     * @param string $word что надо удалить
     * @return string
     */
    public static function lTrimWord($str, $word)
    {
        return preg_replace('/^' . preg_quote($word, '/') . '/u', '', $str);
    }

    /**
     * удаляет $word с конца строки $str
     * @param string $str строка оригинал
     * @param string $word что надо удалить
     * @return string
     */
    public static function rTrimWord($str, $word)
    {
        return preg_replace('/' . preg_quote($word, '/') . '$/u', '', $str);
    }

    /**
     * функция принимает список аргументов и выдает первый не пустой элемент
     * если все пустые то выдает null
     * @return null|mixed
     */
    public static function coalesce()
    {
        foreach (func_get_args() as $arg) {
            if (!empty($arg)) {
                return $arg;
            }
        }
        return null;
    }

    /**
     * создает массив объектов из массива строк
     * @param string $class
     * @param array $dataArray исходный массив сырых данных
     * @param string $indexBy выполнять индексацию массива по этому аттрибуту. По умолчанию используется первичный ключ.
     * Если в состав первичного ключа входит несколько полей то если $indexBy = null выбрасывается исключенение
     * @return BaseActiveRecord[]
     */
    public static function populateArray($class, &$dataArray, $indexBy = null)
    {
        /** @var BaseActiveRecord $class */
        if ($indexBy === null) {
            $pk = $class::primaryKey();
            if (count($pk) > 1) {
                throw new \BadMethodCallException($class . ' use composite primary key. Your must set $indexBy parameter in call Helper::populateArray() ');
            }
            $indexBy = reset($pk);
        }
        $result = [];
        /** @var BaseActiveRecord $obj */
        foreach ($dataArray as $row) {
            $obj = new $class;
            $class::populateRecord($obj, $row);
            $obj->afterFind();
            $result[$obj->$indexBy] = $obj;
        }
        return $result;
    }
}
