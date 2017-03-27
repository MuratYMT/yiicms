<?php
namespace yiicms\models;

use yii\base\InvalidParamException;
use yii\base\Model;
use yiicms\components\core\validators\HtmlFilter;
use yiicms\components\core\validators\PhoneValidator;
use yiicms\models\core\Users;

/**
 * Created by PhpStorm.
 * User: murat
 * Date: 18.02.2017
 * Time: 21:49
 */
class InstallForm extends Model
{
    const SC_REGISTRATION = 'registration';

    public $email;
    public $login;
    public $password;
    public $password2;
    public $timeZone;
    public $phone;

    public function scenarios()
    {
        return [
            self::SCENARIO_DEFAULT => [],
            self::SC_REGISTRATION => ['email', 'login', 'password', 'password2', 'timeZone'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['email', 'login', 'password', 'password2', 'timeZone'], 'required'],
            [['login'], HtmlFilter::class],
            [['login', 'email'], 'string', 'min' => 4, 'max' => 255],
            [['email'], 'email'],
            [['password', 'password2'], 'string', 'min' => 6, 'max' => 255],
            [['password2'], 'compare', 'compareAttribute' => 'password', 'operator' => '==='],
            [['phone'], PhoneValidator::class],
            [['timeZone'], 'in', 'range' => \DateTimeZone::listIdentifiers()],
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
        ];
    }

    /**
     * регистрация
     * @return bool|Users
     * @throws InvalidParamException
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

        $auth = \Yii::$app->authManager;
        $auth->assign($auth->getRole('Super Admin'), $user->userId);

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