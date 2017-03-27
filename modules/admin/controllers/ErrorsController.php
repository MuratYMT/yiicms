<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 30.12.2016
 * Time: 9:38
 */

namespace yiicms\modules\admin\controllers;

use yii\web\Controller;
use yiicms\models\core\Log;
use yiicms\modules\admin\models\ErrorsSearch;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;

class ErrorsController extends Controller
{
    const POPUP_MESSAGE = 'message';

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

    public function actionIndex()
    {
        $model = new ErrorsSearch();
        $dataProvider = $model->search(\Yii::$app->request->queryParams);
        $this->view->title = \Yii::t('yiicms', 'Лог ошибок');
        return $this->render('index', ['model' => $model, 'dataProvider' => $dataProvider]);
    }

    public function actionView($id)
    {
        $model = Log::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException();
        }

        $this->view->title = \Yii::t('yiicms', 'Подробности ошибки');
        return $this->response->openPopup('_view', self::POPUP_MESSAGE, ['model' => $model]);
    }
}
