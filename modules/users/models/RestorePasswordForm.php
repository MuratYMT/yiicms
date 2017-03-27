<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 29.06.2015
 * Time: 16:15
 */

namespace yiicms\modules\users\models;

use yiicms\models\core\Users;
use yii\base\Model;

/**
 * Class ResetPasswordForm форма сброса пароля
 * @package yiicms\modules\users\models
 */
class RestorePasswordForm extends Model
{
    /** @var string новый пароль */
    public $password;

    /** @var string подтверждение пароля */
    public $password2;

    /** @var  string Токен сброса пароля */
    public $token;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['password', 'password2'], 'required'],
            [['password', 'password2'], 'string', 'min' => 6],
            [['password2'], 'compare', 'compareAttribute' => 'password', 'operator' => '==='],
            [
                ['token'],
                'exist',
                'targetClass' => Users::class,
                'message' => \Yii::t('modules/users', 'Неверный токен. Запросите сброс пароля еще раз'),
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'password' => \Yii::t('modules/users', 'Новый пароль'),
            'password2' => \Yii::t('modules/users', 'Подтверждение'),
            'token' => \Yii::t('modules/users', 'Токен'),
        ];
    }

    /**
     * Выполняет установку пароля
     * @return bool
     */
    public function resetPassword()
    {
        if (!$this->validate()) {
            return false;
        }
        $user = Users::findIdentityByAccessToken($this->token);
        $user->password = $this->password;
        if ($user->save()) {
            $user->generateToken();
            return true;
        } else {
            return false;
        }
    }
}
