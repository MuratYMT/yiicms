<?php
/**
 * Created by PhpStorm.
 * User: murat
 * Date: 22.08.2015
 * Time: 23:00
 */

namespace yiicms\components\core\db;

class Connection extends \yii\db\Connection
{
    /**
     * @var  Transaction
     */
    private $_transaction2;

    /**
     * @inheritdoc
     * @throws \yii\db\Exception
     */
    public function beginTransaction($isolationLevel = null)
    {
        $this->open();

        if (($transaction = $this->getTransaction()) === null) {
            $transaction = $this->_transaction2 = new Transaction(['db' => $this]);
        }
        $transaction->begin($isolationLevel);

        return $transaction;
    }

    /**
     * @inheritdoc
     */
    public function getTransaction()
    {
        return $this->_transaction2 && $this->_transaction2->getIsActive() ? $this->_transaction2 : null;
    }

    /**
     * @inheritdoc
     */
    public function close()
    {
        if ($this->pdo !== null) {
            $this->_transaction2 = null;
        }
        parent::close();
    }

    public function open()
    {
        if ($this->pdo !== null) {
            return;
        }

        parent::open();
        $this->setDbTimeZone(\Yii::$app->timeZone);
    }

    /**
     * устанавливает часовой пояс для соединения с базой данных
     * @param string $timeZone часовой пояс
     * @throws \yii\db\Exception
     */
    public function setDbTimeZone($timeZone)
    {
        /*$this->createCommand("SET TIMEZONE TO '$timeZone'")
            ->execute();*/
    }
}
