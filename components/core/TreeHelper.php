<?php
namespace yiicms\components\core;

use yii\base\InvalidParamException;
use yii\db\ActiveRecord;

/**
 * Class TreeHelper реализует работу с деревом
 * @package yiicms\components\core
 */
class TreeHelper
{
    public static $childCountAttribute = '__childCount__';

    /**
     * создает materialized path для элемента
     * @param string $parentMPath materialized path родительского узла, для корневого узла должно иметь значение ''
     * @param integer $itemId идентификатор узла
     * @return string
     */
    public static function makeMPath($parentMPath, $itemId)
    {
        if ($parentMPath !== '') {
            return $parentMPath . '^' . $itemId;
        } else {
            return (string)$itemId;
        }
    }

    /**
     * возвращает mPath в виде массива идентификаторов
     * @param $mPath
     * @return int[]
     */
    public static function mPath2ParentsIds($mPath)
    {
        if (empty($mPath)) {
            return [];
        }
        return explode('^', $mPath);
    }

    public static function mPath2ParentId($mPath)
    {
        $parents = self::mPath2ParentsIds($mPath);
        array_pop($parents);
        return array_pop($parents);
    }

    /**
     * определяет родителя на верху иерархии ветки в которой находится узел
     * @param string $mPath материализованный путь узла для котрого определяется родитель
     * @return int|null
     */
    public static function topParent($mPath)
    {
        $parents = self::mPath2ParentsIds($mPath);
        if (!empty($parents)) {
            return array_shift($parents);
        } else {
            return null;
        }
    }

    /**
     * обновляет mPath у вновь созданного объекта
     * @param ActiveRecord|TreeTrait $activeRecordObject для какого объекта обновить
     */
    public static function setMPath($activeRecordObject)
    {
        $parentMpath = (int)$activeRecordObject->parentId !== 0 ? $activeRecordObject->parent->mPath : '';
        $mPath = self::makeMPath($parentMpath, $activeRecordObject->primaryKeyValue);
        $activeRecordObject->mPath = $mPath;

        $db = $activeRecordObject::getDb();

        $db->createCommand(
            'UPDATE ' . $db->quoteTableName($activeRecordObject::tableName()) . '
             SET ' . $db->quoteColumnName('mPath') . ' = :mPath 
             WHERE ' . $db->quoteColumnName($activeRecordObject->primaryKeyField) . ' = :value',
            [
                ':mPath' => $mPath,
                ':value' => $activeRecordObject->primaryKeyValue,
            ]
        )->execute();
    }

    /**
     * обновляет иерархические данные объекта и его дочерней ветви
     * @param ActiveRecord|TreeTrait $activeRecordObject для какого объекта обновить
     * @return bool
     */
    public static function updateHierarchicalData($activeRecordObject)
    {
        $oldMPath = $activeRecordObject->mPath;
        if ($activeRecordObject->isHierarhyChange) {
            unset($activeRecordObject->parent);

            $parentMpath = (int)$activeRecordObject->parentId !== 0 ? $activeRecordObject->parent->mPath : '';

            $newMPath = self::makeMPath($parentMpath, $activeRecordObject->primaryKeyValue);
            $activeRecordObject->mPath = $newMPath;

            if ($oldMPath === $newMPath) {
                return true;
            }
            //запись не новая могут быть дети. обновляем mPath дочерней ветки
            if (!$activeRecordObject->isNewRecord) {
                $db = $activeRecordObject::getDb();

                $result = $db->createCommand(
                    'UPDATE ' . $db->quoteTableName($activeRecordObject::tableName()) . '
                     SET ' . $db->quoteColumnName('mPath') . ' = replace(' . $db->quoteColumnName('mPath') . ', :oldMPath, :newMPath)
                     WHERE ' . $db->quoteColumnName('mPath') . ' like :oldMPathLike',
                    [
                        ':oldMPath' => $oldMPath . '^',
                        ':newMPath' => $newMPath . '^',
                        ':oldMPathLike' => $oldMPath . '^%',
                    ]
                )->execute();
                return $result !== false;
            }
        }
        return true;
    }

    /**
     * вычисляет уровень узла в дереве по материализованному пути
     * @param string $mPath материализованный путь
     * @return int
     */
    public static function mPath2Level($mPath)
    {
        return count(self::mPath2ParentsIds($mPath));
    }

    /**
     * функция проверяет является ли потенциальный родитель дочерним самого элемента
     * @param string $parentMPath материализованный путь потенциального родителя
     * @param string $elementMPath материализованный путь перемещаемого элемента
     * @return bool true если обнаружена петля
     */
    public static function detectLoop($parentMPath, $elementMPath)
    {
        return ((strlen($elementMPath) <= strlen($parentMPath)) && (strpos($parentMPath, $elementMPath) === 0));
    }

    // ----------------------------------------- функции построения дерева -----------------------------------------------------

    /**
     * формирует дерево
     * @param array $array из какого массива формируется дерево
     * @param string $idField какое поле использовать в качестве иденттификатора
     * @param string|array $sortField по какому полю сортировать узлы на одном уровне
     * если нужна сортировка по нескольким полям то должна иметь следующую структуру
     * [sortField1::string =>[$direction1::int, $asNumeric1::bool], sortField2::string =>[$direction2::int, $asNumeric2::bool], ...]
     * @param integer $direction направление сортировки. Если сортировка по нескольким полям то игнорируется
     * @param boolean $asNumeric при сортировке по $sortField сортировать как число. Если сортировка по нескольким полям то игнорируется
     * @param integer $depth сколько уровней дерева строить если null то все. Если сортировка по нескольким полям то игнорируется
     * @return array
     * @throws InvalidParamException
     */
    public static function build($array, $idField, $sortField, $direction = SORT_ASC, $asNumeric = true, $depth = null)
    {
        switch (count($array)) {
            case 0:
                return $array;
            case 1:
                $fitem = reset($array);
                $fitem[self::$childCountAttribute] = 0;
                return [$fitem];
        }

        $depth = $depth === null ? PHP_INT_MAX : $depth;
        //выполняем предварительную сортировку по полям сортировки
        if (is_string($sortField)) {
            self::sort($array, $asNumeric, $sortField, $direction);
        } else {
            self::multiFieldSort($array, $sortField);
        }

        //перестраиваем исходный массив таким образом чтобы ключами являлись ID родительских узлов,
        //а значениями массив дочерних улов этого родительского узла
        $indexed = [];
        $minLevel = null;
        $parentId = null;
        foreach ($array as $row) {
            $indexed[$row['parentId']][] = $row;
            //ищем заодно начало дерева
            $levelNod = self::mPath2Level($row['mPath']);
            if ($minLevel === null || $levelNod < $minLevel) {
                $minLevel = $levelNod;
                $parentId = $row['parentId'];
            }
        }

        $tree = [];
        self::buildRecursive($indexed, $tree, $parentId, 1, $depth, $idField);
        return $tree;
    }

    /**
     * рекурсивная функция построения дерева
     * @param array $array исходный линейный массив для построения дерева
     * @param array $tree результирющее дерево
     * @param int $parentId родительскийэлемент для текущей итерации
     * @param int $level указатель на текущий уровень дерева
     * @param int $depth до какой глубины строить дерево
     * @param string $idField поле в исходном массиве которое является идентификатором
     * @return int количество дочерних элементов
     */
    private static function buildRecursive(&$array, &$tree, $parentId, $level, $depth, $idField)
    {
        if ($level > $depth || !isset($array[$parentId])) {
            return 0;
        }
        $child = 0;
        foreach ($array[$parentId] as $row) {
            $current = array_push($tree, $row) - 1;
            $tree[$current][self::$childCountAttribute] = self::buildRecursive($array, $tree, $row[$idField], $level + 1, $depth, $idField);
            $child++;
        }
        return $child;
    }

    /**
     * предварительная сортировка по нескольким полям
     * @param array $array исходный массив
     * @param array $sortFields по каким полям сортировать
     * имеет следующую структуру
     * [sortField1::string =>[$direction1::int, $asNumeric1::bool], sortField2::string =>[$direction2::int, $asNumeric2::bool], ...]
     */
    private static function multiFieldSort(&$array, $sortFields)
    {
        usort(
            $array,
            function ($a, $b) use ($sortFields) {
                foreach ($sortFields as $field => $sortOpt) {
                    if ($sortOpt[1]) {
                        if ((double)$a[$field] !== (double)$b[$field]) {
                            $res = ((double)$a[$field] < (double)$b[$field]) ? -1 : 1;
                            return $sortOpt[0] === SORT_ASC ? $res : -$res;
                        }
                    } else {
                        if ((string)$a[$field] !== (string)$b[$field]) {
                            $res = ((string)$a[$field] < (string)$b[$field]) ? -1 : 1;
                            return $sortOpt[0] === SORT_ASC ? $res : -$res;
                        }
                    }
                }
                return 0;
            }
        );
    }

    /**
     * предварительная сортировка по одному полю
     * @param array $array исходный массив
     * @param bool $asNumeric сортировать как число
     * @param string $sortField по какому полю сортировать
     * @param int $direction направление сортировки
     */
    private static function sort(&$array, $asNumeric, $sortField, $direction)
    {
        //сортируем массив по полю сортировки
        if ($asNumeric) {
            usort(
                $array,
                function ($a, $b) use ($sortField, $direction) {
                    if ((double)$a[$sortField] === (double)$b[$sortField]) {
                        return 0;
                    } else {
                        $res = ((double)$a[$sortField] < (double)$b[$sortField]) ? -1 : 1;
                        return $direction === SORT_ASC ? $res : -$res;
                    }
                }
            );
        } else {
            usort(
                $array,
                function ($a, $b) use ($sortField, $direction) {
                    if ((string)$a[$sortField] === (string)$b[$sortField]) {
                        return 0;
                    } else {
                        $res = ((string)$a[$sortField] < (string)$b[$sortField]) ? -1 : 1;
                        return $direction === SORT_ASC ? $res : -$res;
                    }
                }
            );
        }
    }
}
