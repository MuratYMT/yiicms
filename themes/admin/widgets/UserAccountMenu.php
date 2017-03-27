<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 09.03.2017
 * Time: 8:29
 */

namespace yiicms\themes\admin\widgets;

use yii\bootstrap\Widget;
use yiicms\models\core\Users;

class UserAccountMenu extends Widget
{
    /**
     * для какого пользователя строится менюшка
     * @var Users
     */
    public $user;

    public function run()
    {
        return $this->render('user-top-menu', ['user' => $this->user]);
    }
}