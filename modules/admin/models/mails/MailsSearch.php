<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 12.05.2016
 * Time: 16:08
 */

namespace yiicms\modules\admin\models\mails;

use yiicms\models\core\Mails;
use yiicms\models\core\Users;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\data\Sort;

class MailsSearch extends Model
{
    public $sended;
    public $fromLogin;
    public $toLogin;
    public $email;
    public $subject;

    public function rules()
    {
        return [
            [['fromLogin', 'toLogin', 'email', 'subject'], 'string', 'max' => 255],
            [['sended'], 'boolean'],
        ];
    }

    public function scenarios()
    {
        return [
            self::SCENARIO_DEFAULT => ['fromLogin', 'toLogin', 'email', 'subject', 'sended'],
        ];
    }

    public function search($params)
    {
        $query = Mails::find()
            ->with('fromUser');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->sort = new Sort([
            'defaultOrder' => [
                'createdAt' => SORT_DESC
            ],
            'attributes' => [
                'sended' => [
                    'asc' => ['sentAt' => SORT_ASC],
                    'desc' => ['sentAt' => SORT_DESC],
                    'label' => \Yii::t('yiicms', 'Отправлено')
                ],
                'fromLogin' => [
                    'asc' => [Users::tableName() . '.[[login]]' => SORT_ASC],
                    'desc' => [Users::tableName() . '.[[login]]' => SORT_DESC],
                    'label' => \Yii::t('yiicms', 'Логин отправителя')
                ],
                'toLogin',
                'email',
                'subject',
                'createdAt',
                'sentAt'
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        if ($this->sended !== null) {
            $query->andWhere($this->sended ? '[[sentAt]] is not null' : '[[sentAt]] is null');
        }

        if (!empty($this->fromLogin)) {
            $query->andWhere([Users::tableName() . '.[[login]]' => $this->fromLogin]);
        }

        if (!empty($this->toLogin)) {
            $query->andWhere(['toLogin' => $this->toLogin]);
        }

        if (!empty($this->email)) {
            $query->andWhere([Mails::getDb()->driverName === 'pgsql' ? 'ilike' : 'like', 'email', $this->email]);
        }

        if (!empty($this->subject)) {
            $query->andWhere([Mails::getDb()->driverName === 'pgsql' ? 'ilike' : 'like', 'subject', $this->subject]);
        }

        return $dataProvider;
    }
}
