<?php
/**
 * Created by PhpStorm.
 * User: murat
 * Date: 22.08.2015
 * Time: 23:01
 */

namespace yiicms\components\core\db;

class Transaction extends \yii\db\Transaction
{
    private $transactionLevel = 0;

    /**
     * @inheritdoc
     */
    public function begin($isolationLevel = null)
    {
        if ($this->transactionLevel === 0) {
            parent::begin($isolationLevel);
            $this->transactionLevel = 1;
            return;
        }

        \Yii::trace('Hierarhy transaction enter. Level ' . $this->transactionLevel, __METHOD__);
        $this->transactionLevel++;
    }

    /**
     * @inheritdoc
     */
    public function commit()
    {
        $this->transactionLevel--;
        if ($this->transactionLevel === 0) {
            parent::commit();
            return;
        }

        \Yii::trace('Release hierarhy transaction. Level ' . $this->transactionLevel, __METHOD__);
    }

    /**
     * @inheritdoc
     */
    public function rollBack()
    {
        $this->transactionLevel = 0;
        parent::rollBack();
    }
}
