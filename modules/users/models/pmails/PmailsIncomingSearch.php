<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 02.12.2016
 * Time: 17:25
 */

namespace yiicms\modules\users\models\pmails;

use yiicms\models\core\PmailsIncoming;
use yiicms\models\core\Users;
use yii\data\ActiveDataProvider;

class PmailsIncomingSearch extends PmailCommonSearch
{
    /**
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search(array $params)
    {
        $query = PmailsIncoming::view(\Yii::$app->user->id)
            ->joinWith('toUser');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'sentAt' => SORT_DESC,
                    'fromUserLogin' => SORT_ASC,
                ],
                'attributes' => [
                    'sentAt',
                    'fromUserLogin',
                    'subject',
                ],
            ],
        ]);

        if ($this->load($params) && $this->validate()) {
            $query
                ->andFilterWhere([
                    PmailsIncoming::getDb()->driverName === 'pgsql' ? 'ilike' : 'like',
                    Users::tableName() . '.[[login]]',
                    $this->fromUserLogin,
                ])
                ->andFilterWhere([PmailsIncoming::getDb()->driverName === 'pgsql' ? 'ilike' : 'like', 'subject', $this->subject]);
        }

        return $dataProvider;
    }
}
