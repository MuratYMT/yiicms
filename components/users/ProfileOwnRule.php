<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 20.04.2015
 * Time: 9:27
 */

namespace yiicms\components\users;

use yiicms\components\core\rbac\Rule;

/**
 * Class ProfileOwnRule правило определяющее является ли текущий пользователь владельцем профиля пользователя
 * @package sfw\modules\rbac
 */
class ProfileOwnRule extends Rule
{
    /**
     * @inheritDoc
     */
    public function execute($user, $item, $params)
    {
        return (int)$user === (int)$params['profileUserId'];
    }
}
