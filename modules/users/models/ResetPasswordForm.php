<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 29.06.2015
 * Time: 16:15
 */

namespace yiicms\modules\users\models;

use yiicms\components\YiiCms;
use yiicms\models\core\Mails;
use yiicms\models\core\Users;
use yii\base\Model;

/**
 * Class ResetPasswordForm форма сброса пароля
 * @package yiicms\modules\users\models
 */
class ResetPasswordForm extends Model
{
    /**
     * @var string $email E-mail или логин пользователя
     */
    public $email;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['email'], 'required'],
            [['email'], 'string', 'max' => 255],
            [['email'], 'string', 'min' => 3],
            [
                ['email'],
                'exist',
                'targetClass' => Users::class,
                'message' => \Yii::t('modules/users', 'Неизвестный пользователь')
            ]
        ];
    }

    public function attributeLabels()
    {
        return [
            'email' => \Yii::t('modules/users', 'E-mail'),
        ];
    }

    public function sendEmail()
    {
        if (!$this->validate()) {
            return false;
        }
        $user = Users::findByEmail($this->email);

        $user->generateToken();

        $resetLink = \Yii::$app->urlManager->createAbsoluteUrl(['restore-password', 'token' => $user->token]);

        return false !== YiiCms::$app->mailService->send(
                'passwordRestore',
                Users::findById(-1),
                $user,
                ['changeUrl' => $resetLink, 'user' => $user]
            );
    }
}
