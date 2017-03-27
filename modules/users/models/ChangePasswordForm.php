<?php
/**
 * Created by PhpStorm.
 * User: murat
 * Date: 04.07.2015
 * Time: 19:38
 */

namespace yiicms\modules\users\models;

class ChangePasswordForm extends AbstractProfileForm
{
    public $oldPassword;
    public $password;
    public $password2;

    public function rules()
    {
        return [
            [['password', 'password2'], 'required'],
            [['password', 'password2'], 'string', 'min' => 6, 'max' => 255],
            [['oldPassword'], 'string', 'max' => 255],
            [['password2'], 'compare', 'compareAttribute' => 'password', 'operator' => '==='],
        ];
    }

    public function attributeLabels()
    {
        return [
            'oldPassword' => \Yii::t('modules/users', 'Старый пароль'),
            'password' => \Yii::t('modules/users', 'Новый пароль'),
            'password2' => \Yii::t('modules/users', 'Подтверждение')
        ];
    }

    public function changePassword()
    {
        if (!$this->validate()) {
            return false;
        }
        //если меняет сам пользователь то проверяем старый пароль
        if ($this->user->userId === \Yii::$app->user->id && !$this->user->validatePassword($this->oldPassword)) {
            $this->addError('oldPassword', \Yii::t('modules/users', 'Неверный старый пароль'));
            return false;
        }
        $this->user->password = $this->password;
        return $this->user->save();
    }
}
