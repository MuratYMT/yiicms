<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 30.06.2015
 * Time: 15:25
 */

namespace yiicms\components\core;

use yii\base\Model;
use yiicms\models\core\Settings;
use yiicms\models\core\Users;

/**
 * Class LoginForm форма входа
 * @package yiicms\modules\users\models
 */
class LoginForm extends Model
{
    public $email;
    public $password;
    public $rememberMe;

    public function rules()
    {
        return [
            [['email', 'password'], 'required'],
            [['email', 'password'], 'string', 'max' => 255],
            [['rememberMe'], 'boolean'],
            [
                ['password'],
                function ($attribute) {
                    if ($this->hasErrors()) {
                        return;
                    }
                    $user = $this->getUser();
                    if ($user === null || !$user->validatePassword($this->password)) {
                        $this->addError($attribute, \Yii::t('modules/users', 'Неверный пароль или профиль'));
                    }
                },
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'email' => \Yii::t('modules/users', 'E-mail'),
            'password' => \Yii::t('modules/users', 'Пароль'),
            'rememberMe' => \Yii::t('modules/users', 'Запомнить меня'),
        ];
    }

    public function login()
    {
        if (!$this->validate()) {
            return false;
        }
        return \Yii::$app->user->login(
            $this->getUser(),
            $this->rememberMe ? Settings::get('users.loggedInDuration') : 0
        );
    }

    private $_user = false;

    /**
     * @return Users|null
     */
    private function getUser()
    {
        if ($this->_user === false) {
            $this->_user = null;
            if (null !== ($user = Users::findByEmail($this->email)) || null !== ($user = Users::findByLogin($this->email))) {
                $this->_user = $user;
            }
        }

        return $this->_user;
    }
}
