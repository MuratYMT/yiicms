<?php

namespace yiicms\models\core;

use yii\db\ActiveRecord;
use yii\rbac\Role;

/**
 * This is the model class for table "web.blocksForRole".
 * @property integer $blockId
 * @property string $roleName
 * @property Blocks $block пунт меню
 * @property Role $role какая роль
 */
class BlocksForRole extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%blocksForRole}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['blockId', 'roleName'], 'required'],
            [['blockId'], 'integer'],
            [['roleName'], 'string', 'max' => 64],
            [
                ['roleName'],
                function ($attribute) {
                    if ($this->roleName === Settings::get('users.defaultGuestRole')) {
                        return;
                    }
                    if (!$this->hasErrors() && null === \Yii::$app->authManager->getRole($this->roleName)) {
                        $this->addError($attribute, \Yii::t('yiicms', 'Неизвестная роль'));
                    }
                }
            ],
            [['blockId'], 'exist', 'targetClass' => Blocks::class],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'blockId' => \Yii::t('yiicms', 'Block ID'),
            'roleName' => \Yii::t('yiicms', 'Role Name'),
        ];
    }

    // ------------------------------------------------------ связи ---------------------------------------------------

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBlock()
    {
        return $this->hasOne(Blocks::class, ['blockId' => 'blockId']);
    }

    // ---------------------------------------------- геттеры и сеттеры -----------------------------------------------

    public function getRole()
    {
        return \Yii::$app->authManager->getRole($this->roleName);
    }
}
