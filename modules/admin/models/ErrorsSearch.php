<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 30.12.2016
 * Time: 9:40
 */

namespace yiicms\modules\admin\models;

use yiicms\models\core\Log;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class ErrorsSearch extends Model
{
    public $level;
    public $category;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['level'], 'integer'],
            [['category'], 'string', 'max' => 255],
        ];
    }

    public function search($params)
    {
        $query = Log::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['log_time' => SORT_DESC],
            ],
        ]);

        if ($this->load($params) && $this->validate()) {
            $query->andFilterWhere(['level' => $this->level]);
            $query->andFilterWhere([Log::getDb()->driverName === 'pgsql' ? 'ilike' : 'like', 'category', $this->category]);
        }

        return $dataProvider;
    }
}
