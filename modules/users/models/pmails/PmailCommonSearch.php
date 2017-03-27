<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 02.12.2016
 * Time: 17:26
 */

namespace yiicms\modules\users\models\pmails;

use yii\base\Model;

class PmailCommonSearch extends Model
{
    public $fromUserLogin;
    public $toUsersList;
    public $subject;

    public function rules()
    {
        return [
            [['fromUserLogin', 'toUsersList', 'subject'], 'string', 'max' => 64]
        ];
    }
}
