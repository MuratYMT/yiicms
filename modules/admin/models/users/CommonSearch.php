<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 07.07.2015
 * Time: 12:09
 */

namespace yiicms\modules\admin\models\users;

use yii\base\InvalidConfigException;
use yii\base\Model;
use yiicms\models\core\Users;

/**
 * Class UsersPermissionsRoles модель таблицы разрешений назначенных пользователю
 * @package yiicms\models
 */
class CommonSearch extends Model
{
    /** @var Users */
    public $user;

    /**
     * @inheritDoc
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();
        if ($this->user === null) {
            throw new InvalidConfigException(
                \Yii::t('yiicms', '{class}::user должен быть задан', ['class' => parent::class])
            );
        }
    }
}
