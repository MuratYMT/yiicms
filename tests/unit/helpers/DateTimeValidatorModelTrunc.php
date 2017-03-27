<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 29.12.2016
 * Time: 11:31
 */

namespace yiicms\tests\unit\helpers;

use yiicms\components\core\validators\DateTimeValidator;
use yii\base\Model;

class DateTimeValidatorModelTrunc extends Model
{
    public $dt;

    public function rules()
    {
        return [
            [['dt'], DateTimeValidator::className(), 'format' => DateTimeValidator::FORMAT_DATE],
        ];
    }
}
