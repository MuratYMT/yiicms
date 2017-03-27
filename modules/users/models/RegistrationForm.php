<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 30.06.2015
 * Time: 15:25
 */

namespace yiicms\modules\users\models;

use yiicms\components\core\validators\HtmlFilter;
use yiicms\components\core\validators\PhoneValidator;
use yiicms\models\core\Users;
use yii\base\Model;

/**
 * Class RegistrationForm форма регистрации
 * @package yiicms\modules\users\models
 */
class RegistrationForm extends Model
{
    /** регистрация со всеми аттрибутами */
    const SC_FULL_REGISTRATION = 'fullRegistration';
    /** при регистрации не проверяется каптча и требование согласится с правилами */
    const SC_LIGHT_REGISTRATION = 'lightRegistration';

    public $email;
    public $login;
    public $password;
    public $password2;
    public $timeZone;
    public $verifyCode;
    public $phone;
    public $ruleRead;

    public function scenarios()
    {
        return [
            self::SCENARIO_DEFAULT => [],
            self::SC_FULL_REGISTRATION => ['email', 'login', 'password', 'password2', 'timeZone', 'ruleRead', 'verifyCode'],
            self::SC_LIGHT_REGISTRATION => ['email', 'login', 'password', 'password2', 'timeZone'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['email', 'login', 'password', 'password2', 'timeZone', 'ruleRead', 'verifyCode'], 'required'],
            [['login'], HtmlFilter::class],
            [['login', 'email'], 'string', 'min' => 4, 'max' => 255],
            [
                ['login'],
                function ($attribute) {
                    //проверяем логин на уникальность
                    if ($this->hasErrors()) {
                        return;
                    }
                    $user = Users::findOne(['lower(login)' => strtolower($this->login)]);
                    if ($user !== null) {
                        $this->addError($attribute, \Yii::t('modules/users', 'Вы не можете использовать этот логин'));
                    }
                },
            ],
            [['email'], 'email'],
            [
                ['email'],
                function ($attribute) {
                    //проверяем уникальность мыла
                    if ($this->hasErrors()) {
                        return;
                    }
                    $user = Users::findOne(['lower(email)' => $this->email]);
                    if ($user !== null) {
                        $this->addError($attribute, \Yii::t('modules/users', 'Вы не можете использовать этот Email'));
                    }
                },
            ],
            [['password', 'password2'], 'string', 'min' => 6, 'max' => 255],
            [['password2'], 'compare', 'compareAttribute' => 'password', 'operator' => '==='],
            [['phone'], PhoneValidator::class],
            [['timeZone'], 'in', 'range' => \DateTimeZone::listIdentifiers()],
            [['ruleRead'], 'boolean'],
            [
                ['ruleRead'],
                function ($attribute) {
                    //проверка согласия с правилами
                    if (!$this->ruleRead && !$this->hasErrors()) {
                        $this->addError($attribute, \Yii::t('modules/users', 'Вы должны согласится с правилами'));
                    }
                },
            ],
            [['verifyCode'], 'captcha', 'captchaAction' => 'site/captcha', 'enableClientValidation' => false],
        ];
    }

    public function attributeLabels()
    {
        return [
            'email' => \Yii::t('modules/users', 'E-mail'),
            'login' => \Yii::t('modules/users', 'Имя (Логин) пользователя'),
            'phone' => \Yii::t('modules/users', 'Мобильный телефон'),
            'password' => \Yii::t('modules/users', 'Пароль'),
            'password2' => \Yii::t('modules/users', 'Подтверждение'),
            'timeZone' => \Yii::t('modules/users', 'Часовой пояс'),
            'ruleRead' => \Yii::t('modules/users', 'Я принимаю условия использования'),
            'verifyCode' => \Yii::t('modules/users', 'Проверочный код'),
        ];
    }

    /**
     * регистрация
     * @return bool|Users
     * @throws \yii\base\InvalidParamException
     */
    public function registration()
    {
        if (!$this->validate()) {
            return false;
        }

        $user = new Users();
        $user->scenario = Users::SC_REGISTRATION;
        $user->attributes = $this->attributes;
        $user->password = $this->password;

        if ($user->save() === false) {
            $this->addErrors($user->errors);
        }
        return $user;
    }

    /**
     * @return array список доступных часовых поясов
     */
    public function availableTimeZones()
    {
        $tz1 = \DateTimeZone::listIdentifiers();
        return array_combine($tz1, $tz1);
    }
}
