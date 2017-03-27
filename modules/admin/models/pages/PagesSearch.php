<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 01.09.2015
 * Time: 8:23
 */

namespace yiicms\modules\admin\models\pages;

use yiicms\models\content\Page;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class PagesSearch extends Page
{
    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['ownerLogin', 'title', 'lang', 'slug'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = Page::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([Page::getDb()->driverName === 'pgsql' ? 'ilike' : 'like', 'ownerLogin', $this->ownerLogin]);
        $query->andFilterWhere([Page::getDb()->driverName === 'pgsql' ? 'ilike' : 'like', 'title', $this->title]);
        $query->andFilterWhere([Page::getDb()->driverName === 'pgsql' ? 'ilike' : 'like', 'lang', $this->lang]);
        $query->andFilterWhere([Page::getDb()->driverName === 'pgsql' ? 'ilike' : 'like', 'slug', $this->slug]);

        return $dataProvider;
    }
}
