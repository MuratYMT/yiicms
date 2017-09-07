<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 21.01.2016
 * Time: 11:34
 */

namespace yiicms\modules\admin\models\blocks;

use yiicms\models\core\BlocksVisibleForPathInfo;
use yii\data\ActiveDataProvider;
use yiicms\models\core\constants\VisibleForPathInfoConst;

class BlocksVisibleForPathInfoSearch extends BlocksVisibleForPathInfo
{
    public function rules()
    {
        return [
            [['rule'], 'in', 'range' => VisibleForPathInfoConst::RULES_ARRAY],
            [['template'], 'string', 'max' => 255],
        ];
    }

    public function search($blockId)
    {
        $query = BlocksVisibleForPathInfo::find()
            ->where(['blockId' => $blockId]);

        $dataProvider = new ActiveDataProvider(['query' => $query]);

        if (!$this->validate()) {
            return $dataProvider;
        }

        return $dataProvider;
    }
}
