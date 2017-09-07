<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 13.01.2016
 * Time: 9:23
 */

namespace yiicms\modules\admin\models\blocks;

use yiicms\models\core\Blocks;
use yiicms\models\core\constants\VisibleForPathInfoConst;
use yiicms\models\core\VisibleForPathInfo;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class BlocksSearch extends Model
{
    public $title;
    public $description;
    public $position;
    public $weight;
    public $activy;
    public $pathInfoVisibleOrder;

    public function rules()
    {
        return [
            [['title', 'description'], 'string', 'max' => 255],
            [['position'], 'string', 'max' => 255],
            [['weight', 'pathInfoVisibleOrder'], 'integer'],
            [['activy'], 'in', 'range' => [0, 1]],
            [['pathInfoVisibleOrder'], 'in', 'range' => VisibleForPathInfoConst::VISIBLE_ARRAY],
        ];
    }

    public function search($params)
    {
        $query = Blocks::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if ($this->load($params) && $this->validate()) {
            if (!empty($this->title)) {
                $query->andFilterWhere([Blocks::getDb()->driverName === 'pgsql' ? 'ilike' : 'like', 'trgmIndex', $this->title . '%', false]);
            }
            if (!empty($this->description)) {
                $query->andFilterWhere([Blocks::getDb()->driverName === 'pgsql' ? 'ilike' : 'like', 'description', $this->description . '%', false]);
            }

            $query->andFilterWhere(['position' => $this->position])
                ->andFilterWhere(['weight' => $this->weight])
                ->andFilterWhere(['activy' => $this->activy])
                ->andFilterWhere(['pathInfoVisibleOrder' => $this->pathInfoVisibleOrder]);
        }

        return $dataProvider;
    }
}
