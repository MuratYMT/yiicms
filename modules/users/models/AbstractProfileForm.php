<?php
/**
 * Created by PhpStorm.
 * User: murat
 * Date: 04.07.2015
 * Time: 21:01
 */

namespace yiicms\modules\users\models;

use yiicms\models\core\Users;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\web\NotFoundHttpException;

/**
 * Class AbstractProfileForm
 * @package yiicms\modules\users\models
 * @property Users $user ReadOnly
 * @property int $userId WriteOnly
 */
class AbstractProfileForm extends Model
{
    /**
     * @var bool|Users
     */
    private $_user = false;

    public function init()
    {
        parent::init();
        if ($this->_user === false) {
            throw new InvalidConfigException('You must set property' . static::class . '::userId before use');
        }
    }

    /**
     * находит пользователя
     * @return Users
     */
    public function getUser()
    {
        return $this->_user;
    }

    public function setUserId($userId)
    {
        if ($this->_user === false) {
            $this->_user = $userId !== null ? Users::findByIdWithAllStatus($userId) : null;
        }
        if ($this->_user === null) {
            throw new NotFoundHttpException;
        }
    }
}
