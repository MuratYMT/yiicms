<?php
/**
 * Created by PhpStorm.
 * User: murat
 * Date: 04.07.2015
 * Time: 19:38
 */

namespace yiicms\modules\admin\models\users;

use yiicms\models\core\Users;
use yii\base\Model;
use yii\web\NotFoundHttpException;

class ChangePasswordForm extends Model
{
    public $password;
    public $password2;

    public function rules()
    {
        return [
            [['password', 'password2'], 'required'],
            [['password', 'password2'], 'string', 'min' => 6, 'max' => 255],
            [['password2'], 'compare', 'compareAttribute' => 'password', 'operator' => '===',],
        ];
    }

    public function attributeLabels()
    {
        return [
            'password' => \Yii::t('yiicms', 'Пароль'),
            'password2' => \Yii::t('yiicms', 'Подтверждение')
        ];
    }

    /**
     * @param int $userId
     * @return bool|Users
     * @throws NotFoundHttpException
     */
    public function changePassword($userId)
    {
        if (!$this->validate()) {
            return false;
        }
        /** @var Users $user */
        if (null === $user = Users::findOne($userId)) {
            throw new NotFoundHttpException;
        }
        $user->password = $this->password;
        return $user->save() ? $user : false;
    }
}
