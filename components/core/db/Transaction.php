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
    private $_level2 = 0;

    /**
     * @inheritdoc
     */
    public function begin($isolationLevel = null)
    {
        if ($this->_level2 === 0) {
            parent::begin($isolationLevel);
            $this->_level2 = 1;
            return;
        }

        \Yii::trace('Hierarhy transaction enter. Level ' . $this->_level2, __METHOD__);
        $this->_level2++;
    }

    /**
     * @inheritdoc
     */
    public function commit()
    {
        $this->_level2--;
        if ($this->_level2 === 0) {
            parent::commit();
            return;
        }

        \Yii::trace('Release hierarhy transaction. Level ' . $this->_level2, __METHOD__);
    }

    /**
     * @inheritdoc
     */
    public function rollBack()
    {
        $this->_level2 = 0;
        parent::rollBack();
    }
}
