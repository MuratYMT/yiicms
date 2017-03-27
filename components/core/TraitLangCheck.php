<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 13.12.2016
 * Time: 8:55
 */

namespace yiicms\components\core;

use codemix\localeurls\UrlManager;
use yii\web\NotFoundHttpException;

trait TraitLangCheck
{
    /**
     * проверяет переданный язык на наличие в системе и если надо устанавливает язык по умолчанию
     * @param string $lang
     * @return string
     * @throws NotFoundHttpException
     */
    protected static function checkLang($lang)
    {
        /** @var UrlManager $urlManager */
        $urlManager = \Yii::$app->urlManager;

        if ($lang === null) {
            $lang = \Yii::$app->language;
        } elseif (!in_array($lang, $urlManager->languages, true)) {
            throw new NotFoundHttpException;
        }
        return $lang;
    }
}