<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 22.01.2016
 * Time: 9:03
 */

namespace yiicms\components\core;

use yii\web\Response;

class Url extends \yii\helpers\Url
{
    const RETURN_PARAM = 'returnURL';

    /**
     * выполняет те же действия что и Url::to но добавляет в возвратный параметр (__return) сгенерированный из строки url текущего запроса
     * @param string|array $url @see to
     * @param bool $scheme @see to
     * @param bool $includeParams добавлять параметры из текущей строки запроса в новую
     * @return string
     */
    public static function toWithNewReturn($url = '', $scheme = false, $includeParams = false)
    {
        $url = (array)$url;

        if ($includeParams) {
            $queryParams = \Yii::$app->request->queryParams;
            $url = array_merge($url, $queryParams);
        }
        $url[self::RETURN_PARAM] = self::generateReturnUrl();
        return parent::to($url, $scheme);
    }

    /**
     * определяет есть ли возвратная ссылка
     * @return bool
     */
    public static function issetReturn()
    {
        return self::getGeneratedReturnUrl() !== null;
    }

    /**
     * выполняет те же действия что и Url::to но добавляет в возвратный параметр (__return) взятый из возвратного параметра (__return)
     * из строки url текущего запроса
     * @param string|array $url @see to
     * @param bool $scheme @see to
     * @param bool $includeParams добавлять параметры из текущей строки запроса в новую
     * @return string
     */
    public static function toWithCurrentReturn($url = '', $scheme = false, $includeParams = false)
    {
        $url = (array)$url;
        if ($includeParams) {
            $queryParams = \Yii::$app->request->queryParams;
            $url = array_merge($url, $queryParams);
        }
        $url[self::RETURN_PARAM] = self::getGeneratedReturnUrl();
        return parent::to($url, $scheme);
    }

    /**
     * берет текущий pathInfo и queryParams и добавляет к ним переданные параметры все остальные действия аналогичны Url::to
     * @param array $params
     * @param bool $scheme
     * @return string
     */
    public static function toCurrent(array $params = [], $scheme = false)
    {
        $request = \Yii::$app->request;
        $url = '/' . $request->pathInfo;
        $queryParams = $request->queryParams;
        $queryParams = array_merge($queryParams, $params);
        array_unshift($queryParams, $url);
        return parent::to($queryParams, $scheme);
    }

    /**
     * добавляет к уже существующей строке еще параметры
     * @param string $queryString строка к которой надо добавить парметры
     * @param array $newParams key-value массив добавляемых параметров
     * @param bool $scheme @see [[Url::to()]]
     * @return string
     */
    public static function appendQueryParams($queryString, $newParams, $scheme = false)
    {
        $url = parse_url($queryString);
        $queryParams = [];
        parse_str($url['query'], $queryParams);
        $queryParams = array_merge($queryParams, $newParams);
        $path = Helper::lTrimWord($url['path'], '/' . \Yii::$app->language);
        array_unshift($queryParams, $path);
        return parent::to($queryParams, $scheme);
    }

    private static $_generatedReturnUrl;

    /**
     * кодирует в base64 url и параметры текущего запроса
     * @return string
     */
    public static function generateReturnUrl()
    {
        if (self::$_generatedReturnUrl === null) {
            $url = parse_url(\Yii::$app->request->url);
            $returnUrlParams = [];
            if (isset($url['query'])) {
                $parts = explode('&', $url['query']);
                foreach ($parts as $part) {
                    $pieces = explode('=', $part);
                    if (count($pieces) === 2 && strlen($pieces[1]) > 0) {
                        $returnUrlParams[] = $part;
                    }
                }
            }
            $result = $url['path'];

            if (count($returnUrlParams) > 0) {
                $result .= '?' . implode('&', $returnUrlParams);
            }

            self::$_generatedReturnUrl = base64_encode($result);
        }
        return self::$_generatedReturnUrl;
    }

    /**
     * декодирует возвратном параметре (__return) из get параметра запроса
     * @return string
     */
    public static function decodeReturnUrl()
    {
        $url = self::getGeneratedReturnUrl();

        if (false === $res = @base64_decode($url)) {
            $res = '#';
        }
        return $res;
    }

    /**
     * выдает возвратном параметре (__return) из get параметра запроса. если его нет то пустая строка
     * @return string
     */
    public static function getGeneratedReturnUrl()
    {
        return \Yii::$app->request->get(self::RETURN_PARAM, null);
    }

    /**
     * @param string $defaultUrl
     * @return Response
     */
    public static function goReturn($defaultUrl = null)
    {
        if ($defaultUrl === null) {
            $defaultUrl = Url::decodeReturnUrl();
        }
        return \Yii::$app->getResponse()->redirect(Url::to($defaultUrl), 303);
    }
}
