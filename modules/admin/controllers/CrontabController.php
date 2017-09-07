<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 01.02.2016
 * Time: 9:06
 */

namespace yiicms\modules\admin\controllers;

use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yiicms\components\core\Url;
use yiicms\components\core\widgets\Alert;
use yii\web\Controller;
use yiicms\components\YiiCms;
use yiicms\models\core\Crontabs;
use yiicms\modules\admin\models\CrontabsSearch;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;

class CrontabController extends Controller
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
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'add'=>['POST', 'GET'],
                    'edit'=>['POST', 'GET'],
                    'index'=>['POST', 'GET'],
                    'del' => ['POST'],
                    'run' => ['POST'],
                    '*' => [],
                ]
            ]
        ];
    }

    /**
     * страница меню
     */
    public function actionIndex()
    {
        $request = \Yii::$app->request;
        $model = new CrontabsSearch();

        $dataProvider = $model->search($request->queryParams);
        $this->view->title = \Yii::t('yiicms', 'Планировщик заданий');

        return $this->render('index', ['dataProvider' => $dataProvider, 'model' => $model]);
    }

    public function actionAdd()
    {
        $request = \Yii::$app->request;

        $model = new Crontabs();
        $model->scenario = Crontabs::SC_EDIT;
        /** @noinspection NotOptimalIfConditionsInspection */
        if ($request->isPost && $model->load($request->post()) && $model->save()) {
            Alert::success(\Yii::t('yiicms', 'Задание "{job}" добавлено', ['job' => $model->descript]));
            return Url::goReturn();
        }

        $this->view->title = \Yii::t('yiicms', 'Добавить задание');
        return $this->render('cronjob-edit', ['model' => $model]);
    }

    public function actionEdit($jobClass)
    {
        $request = \Yii::$app->request;

        $model = self::findJob($jobClass);

        $model->scenario = Crontabs::SC_EDIT;

        /** @noinspection NotOptimalIfConditionsInspection */
        if ($request->isPost && $model->load($request->post()) && $model->save()) {
            Alert::success(\Yii::t('yiicms', 'Задание "{job}" изменено', ['job' => $model->descript]));
            return Url::goReturn();
        }

        $this->view->title = \Yii::t('yiicms', 'Изменить задание "{job}"', ['job' => $model->descript]);
        return $this->render('cronjob-edit', ['model' => $model]);
    }

    public function actionDel($jobClass)
    {
        $model = self::findJob($jobClass);

        if ($model->delete()) {
            Alert::success(\Yii::t('yiicms', 'Задание "{job}" удалено', ['job' => $model->descript]));
        } else {
            Alert::error(\Yii::t('yiicms', 'Ошибка удаления задания'));
        }
        return Url::goReturn();
    }

    public function actionRun($jobClass)
    {
        $job = self::findJob($jobClass);

        if (YiiCms::$app->crontabService->runJob($job)) {
            Alert::success(\Yii::t('yiicms', 'Задание "{job}" было запущено', ['job' => $job->descript]));
        } else {
            Alert::error(\Yii::t('yiicms', 'Ошибка запуска задания'));
        }
        return Url::goReturn();
    }

    /**
     * @param $jobClass
     * @return Crontabs
     * @throws NotFoundHttpException
     */
    private static function findJob($jobClass)
    {
        if (null === ($model = Crontabs::findOne($jobClass))) {
            throw new NotFoundHttpException;
        }
        return $model;
    }
}
