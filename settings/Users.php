<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 20.02.2017
 * Time: 17:10
 */

namespace yiicms\settings;

use yii\bootstrap\ActiveForm;
use yiicms\components\admin\SettingsGroup;
use yiicms\components\core\ArrayHelper;
use yiicms\components\core\SettingsBlock;

class Users extends SettingsBlock
{
    /**
     * @inheritdoc
     */
    public function getSettings()
    {
        return [
            'main' => [
                'passwordResetTokenExpire' => [
                    'title' => \Yii::t('modules/users', 'Cрок действия токена для сброса пароля'),
                    'value' => 3600,
                    'rules' => [
                        ['integer', 'min' => 0],
                    ],
                ],
                'loggedInDuration' => [
                    'title' => \Yii::t(
                        'modules/users',
                        'Время в течении которых пользователь остается залогиненым при установленном параметре "запомнить меня"'
                    ),
                    'value' => 2592000,
                    'rules' => [
                        ['integer', 'min' => 0],
                    ],
                ],
                'multiLogin' => [
                    'title' => \Yii::t('modules/users', 'Возможность параллельной авторизации из разных мест'),
                    'value' => 0,
                    'rules' => [
                        ['boolean'],
                    ],
                ],
                'defaultRegisteredRole' => [
                    'title' => \Yii::t('modules/users', 'Роль зарегестрированного пользователя по умолчанию'),
                    'value' => 'Registered Users',
                    'rules' => [
                        ['string', 'max' => 64],
                        ['in', 'range' => ArrayHelper::getColumn(\Yii::$app->authManager->getRoles(), 'name')],
                    ],
                ],
                'defaultGuestRole' => [
                    'title' => \Yii::t('modules/users', 'Роль гостя по умолчанию'),
                    'value' => '__GUEST__',
                    'rules' => [
                        ['string', 'max' => 64],
                        ['in', 'range' => ArrayHelper::getColumn(\Yii::$app->authManager->getRoles(), 'name')],
                    ],
                ],
            ],
            'pmails' => [
                'alertBlockTimeout' => [
                    'title' => \Yii::t(
                        'modules/users',
                        'Время блокирования всплывающего окна с сообщением о новых личных сообщениях после последнего показа'
                    ),
                    'value' => 3600,
                    'rules' => [
                        ['integer', 'min' => 0],
                    ],
                ],
            ],
        ];
    }

    public function getSettingsGroupTitle($name = null)
    {
        $titles = [
            'main' => \Yii::t('modules/users', 'Пользователи'),
            'pmails' => \Yii::t('modules/users', 'Личные сообщения'),
        ];

        return $name === null ? $titles : $titles[$name];
    }

    public function renderPmailsSettings(ActiveForm $form, SettingsGroup $model)
    {
        ob_start();
        echo $form->field($model, 'alertBlockTimeout')->textInput();
        return ob_get_clean();
    }

    public function renderMainSettings(ActiveForm $form, SettingsGroup $model)
    {
        ob_start();

        echo $form->field($model, 'passwordResetTokenExpire')->textInput();
        echo $form->field($model, 'loggedInDuration')->textInput();
        echo $form->field($model, 'multiLogin')->checkbox();

        $items = ArrayHelper::getColumn(\Yii::$app->authManager->getRoles(), 'name');
        sort($items);

        echo $form->field($model, 'defaultRegisteredRole')->dropDownList(array_combine($items, $items));
        echo $form->field($model, 'defaultGuestRole')->dropDownList(array_combine($items, $items));

        return ob_get_clean();
    }
}