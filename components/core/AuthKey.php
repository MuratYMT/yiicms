<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 20.05.2016
 * Time: 14:39
 */

namespace yiicms\components\core;

use yii\base\Object;
use yiicms\models\core\Settings;

class AuthKey extends Object
{
    /** @var string ключ авторизации */
    public $key;
    /** @var  int timestamp срока дейтсивия */
    public $expire;
    /** @var  string с какого ip была авторизация */
    public $ip;

    public function asArray()
    {
        return ['key' => $this->key, 'expire' => $this->expire, 'ip' => $this->ip];
    }

    /**
     * проверяет ключ
     * @param string $authKey
     * @return bool
     */
    public function check($authKey)
    {
        return ($this->key === $authKey && time() < $this->expire);
    }

    public static function createFormJson($json)
    {
        $array = @json_decode($json, true);
        if (!is_array($array)) {
            return [];
        }

        /** @var array $array */
        $result = [];
        foreach ($array as $item) {
            $obj = new self($item);
            $result[$obj->key] = $obj;
            if (!Settings::get('users.multiLogin')) {
                break;
            }
        }
        return $result;
    }

    /**
     * @param AuthKey[]|AuthKey $value
     * @return string
     */
    public static function saveToJson($value)
    {
        /** @var AuthKey[] $value */
        $value = ArrayHelper::asArray($value);

        $result = [];
        foreach ($value as $authKey) {
            $result[$authKey->key] = $authKey->asArray();
            if (!Settings::get('users.multiLogin')) {
                break;
            }
        }
        return json_encode($result, JSON_FORCE_OBJECT);
    }

    public static function create()
    {
        $authKey = new self;
        $authKey->key = \Yii::$app->security->generateRandomString();
        $authKey->ip = \Yii::$app->request->userIP;
        $authKey->expire = time() + Settings::get('users.loggedInDuration');
        return $authKey;
    }
}
