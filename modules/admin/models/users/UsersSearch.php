<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 15.09.2015
 * Time: 15:07
 */

namespace yiicms\modules\admin\models\users;

use yiicms\components\core\ArrayHelper;
use yiicms\models\core\Users;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class UsersSearch extends Model
{
    public $login;
    public $email;
    public $fio;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['login', 'fio', 'email'], 'string', 'max' => 64],
            [['email'], 'email'],
        ];
    }

    /**
     * Creates data provider instance with search query applied
     * @param array $params
     * @param bool $activeOnly искать только активных
     * @return ActiveDataProvider
     * @throws \yii\base\InvalidParamException
     */
    public function search($params, $activeOnly = false)
    {
        $query = Users::find();

        if ($activeOnly) {
            $query->where(['status' => Users::STATUS_ACTIVE]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if ($this->load($params) && $this->validate()) {
            $query->andFilterWhere([Users::getDb()->driverName === 'pgsql' ? 'ilike' : 'like', 'login', $this->login])
                ->andFilterWhere([Users::getDb()->driverName === 'pgsql' ? 'ilike' : 'like', 'fio', $this->fio])
                ->andFilterWhere([Users::getDb()->driverName === 'pgsql' ? 'ilike' : 'like', 'email', $this->email]);
        }

        return $dataProvider;
    }

    /**
     * используется для построения колонки назначенных ролей в таьлице пользователей
     * @param int $userId
     * @return string
     */
    public static function rolesForUser($userId)
    {
        return implode(', ', ArrayHelper::getColumn(\Yii::$app->authManager->getAssignments($userId), 'roleName'));
    }
}
