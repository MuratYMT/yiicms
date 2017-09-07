<?php
/**
 * Created by PhpStorm.
 * User: murat
 * Date: 03.09.2017
 * Time: 23:05
 */

namespace yiicms\services;

use yiicms\models\core\constants\VisibleForPathInfoConst;
use yiicms\models\core\VisibleForPathInfo;

trait VisibleForPathTrait
{


    /**
     * функция удаляет пункты меню/блоки которые не должны быть видны на этой странице
     * @param array $dataRaw сырые строки из таблицы базы данных из которых надо удалить невидимые объекты
     * обязательное условия: 1. в качестве ключей массива должны использоваться идентификаторы объектов
     * 2. должна быть колонка 'pathInfoVisibleOrder' определяющая порядок прменения правил
     * @param string $primaryKey
     * @param VisibleForPathInfo[] $allVisibleForPathInfo
     */
    public function clearObjectsForThisPage(&$dataRaw, $primaryKey, $allVisibleForPathInfo)
    {
        //создаем массив блоков для минимизации обработки в последующих этапах
        $templates = [];
        foreach ($allVisibleForPathInfo as $row) {
            $templates[$row->$primaryKey][] = $row;
        }

        $pathInfo = \Yii::$app->request->pathInfo;
        //удаляем объекты которые не видны на этой транице
        foreach ($dataRaw as $itemId => $row) {
            switch ($row['pathInfoVisibleOrder']) {
                case VisibleForPathInfoConst::VISIBLE_DENY_ALLOW:
                    //сперва запретить везде, потом разрешить
                    if (!isset($templates[$itemId]) ||        //правила не определены значит невидно нигде
                        //разрешающих правил для этого объекта нет
                        (isset($templates[$itemId]) && !$this->testTemplates($templates[$itemId], $pathInfo))
                    ) {
                        //разрешения нет
                        unset($dataRaw[$itemId]);
                    }
                    break;
                case VisibleForPathInfoConst::VISIBLE_ALLOW_DENY:
                    //сперва разрешить везде, потом запретить
                    if (isset($templates[$itemId]) //есть запрещающее правило
                        && $this->testTemplates($templates[$itemId], $pathInfo)
                    ) {
                        //запрещение есть запрещаем
                        unset($dataRaw[$itemId]);
                    }
                    break;
            }
        }
    }

    /**
     * проверяет видим ли пункт меню на этой странице
     * @param  VisibleForPathInfo[] $templates массив шаблонов
     * @param string $pathInfo URN текущей страницы
     * @return bool
     */
    private function testTemplates($templates, $pathInfo)
    {
        foreach ($templates as $template) {
            //для каждого правила обработки шаблона вызываем свою функцию поиска совпадений
            switch ($template->rule) {
                case VisibleForPathInfoConst::RULE_CONTAIN:
                    $found = mb_strpos($pathInfo, $template->template) !== false;
                    break;
                case VisibleForPathInfoConst::RULE_BEGIN:
                    $found = mb_strpos($pathInfo, $template->template) === 0;
                    break;
                case VisibleForPathInfoConst::RULE_END:
                    $found = mb_strpos(strrev($pathInfo), strrev($template->template)) === 0;
                    break;
                case VisibleForPathInfoConst::RULE_EQUAL:
                    $found = $pathInfo === $template->template;
                    break;
                case VisibleForPathInfoConst::RULE_PCRE:
                    $found = preg_match('/' . $template->template . '/', $pathInfo) ? true : false;
                    break;
                default:
                    $found = false;
            }
            if ($found) {
                return true;
            }
        }

        return false;
    }
}