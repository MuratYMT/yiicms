<?php
/**
 * Created by PhpStorm.
 * User: murat
 * Date: 01.02.2016
 * Time: 21:43
 */

namespace yiicms\modules\users\models\pmails;

use yiicms\models\core\PmailsOutgoing;
use yii\data\ActiveDataProvider;

class PmailsOutgoingSearch extends PmailCommonSearch
{
    /**
     * @param array $params
     * @param bool $draft
     * @return ActiveDataProvider
     */
    public function search(array $params, $draft = false)
    {
        $query = PmailsOutgoing::view(\Yii::$app->user->id)
            ->joinWith('fromUser')
            ->andWhere(['sended' => $draft ? 0 : 1]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'sentAt' => SORT_DESC,
                ],
                'attributes' => [
                    'sentAt',
                    'subject',
                ],
            ],
        ]);

        if ($this->load($params) && $this->validate()) {
            $query
                ->andFilterWhere([PmailsOutgoing::getDb()->driverName === 'pgsql' ? 'ilike' : 'like', 'trgmToUsersIndex', $this->toUsersList])
                ->andFilterWhere([PmailsOutgoing::getDb()->driverName === 'pgsql' ? 'ilike' : 'like', 'subject', $this->subject]);
        }

        return $dataProvider;
    }
}
