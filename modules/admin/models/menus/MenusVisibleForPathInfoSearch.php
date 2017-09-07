<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 25.01.2016
 * Time: 11:21
 */

namespace yiicms\modules\admin\models\menus;

use yiicms\models\core\constants\VisibleForPathInfoConst;
use yiicms\models\core\MenusVisibleForPathInfo;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class MenusVisibleForPathInfoSearch extends Model
{
    public $rule;
    public $template;

    public function rules()
    {
        return [
            [['rule'], 'in', 'range' => VisibleForPathInfoConst::RULES_ARRAY],
            [['template'], 'string', 'max' => 255],
        ];
    }

    public function search($menuId)
    {
        $query = MenusVisibleForPathInfo::find()
            ->where(['menuId' => $menuId]);

        return new ActiveDataProvider(['query' => $query]);
    }
}
