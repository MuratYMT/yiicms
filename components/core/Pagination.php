<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 08.09.2016
 * Time: 10:01
 */

namespace yiicms\components\core;

/**
 * Class Pagination
 * @package yiicms\components\core
 * @property array $fromTo массив ['from' => xxx, 'to' => xxx] для указания границ выборки. обысно используется при выводе сопроводительной строки
 *     таблицы. readOnly
 */
class Pagination extends \yii\data\Pagination
{
    public function getFromTo()
    {
        $to = $this->offset + $this->limit;

        return [
            'from' => (int)$this->totalCount === 0 ? 0 : $this->offset + 1,
            'to' => $to > $this->totalCount ? $this->totalCount : $to
        ];
    }
}
