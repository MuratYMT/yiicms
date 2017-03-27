<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 02.08.2016
 * Time: 8:46
 */

namespace yiicms\modules\admin\controllers;

use yiicms\components\core\widgets\Alert;
use yii\web\Controller;
use yiicms\components\admin\SettingsGroup;
use yii\filters\AccessControl;
use yiicms\models\core\Settings;

class SettingsController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['Admin'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param string $settingsGroup текущая группа настроек
     * @return string
     */
    public function actionIndex($settingsGroup = null)
    {
        $request = \Yii::$app->request;
        $models = self::scanBlocks();

        ksort($models, SORT_STRING);

        $model = null;
        foreach ($models as $obj) {
            if ($obj->group === $settingsGroup) {
                $model = $obj;
                break;
            }
        }

        if ($model === null) {
            $model = reset($models);
            $settingsGroup = $model->group;
        }

        if ($request->isPost) {
            /** @noinspection NotOptimalIfConditionsInspection */
            if ($model->load($request->post()) && $model->save()) {
                Alert::success(\Yii::t('yiicms', 'Настройки сохранены'));
            } else {
                Alert::error(\Yii::t('yiicms', 'Ошибка сохранения'));
            }
        }

        $this->view->title = \Yii::t('yiicms', 'Настройки сайта');

        return $this->render('index', ['models' => $models, 'settingsGroup' => $settingsGroup]);
    }

    /**
     * @return SettingsGroup[]
     */
    private static function scanBlocks()
    {
        $result = [];
        foreach (Settings::scanBlocks() as $block) {
            $groups = array_keys($block->getSettingsGroupTitle());
            foreach ($groups as $groupName) {
                $settingsGroup = new SettingsGroup(['settingsBlock' => $block, 'groupName' => $groupName]);
                $result[$settingsGroup->group] = $settingsGroup;
            }
        }
        return $result;
    }
}
