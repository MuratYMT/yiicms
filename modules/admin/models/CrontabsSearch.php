<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 01.02.2016
 * Time: 9:17
 */

namespace yiicms\modules\admin\models;

use yiicms\models\core\Crontabs;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class CrontabsSearch extends Model
{
    public $descript;
    public $runTime;

    public function rules()
    {
        return [
            [['descript', 'runTime'], 'string', 'max' => 64],
        ];
    }

    public function search($params)
    {
        $query = Crontabs::find();

        $dataProvider = new ActiveDataProvider(['query' => $query]);

        if ($this->load($params) && $this->validate()) {
            if (!empty($this->descript)) {
                $query->andWhere([Crontabs::getDb()->driverName === 'pgsql' ? 'ilike' : 'like', 'descript', $this->descript . '%', false]);
            }

            $query->andFilterWhere(['runTime' => $this->runTime]);
        }

        return $dataProvider;
    }
}
