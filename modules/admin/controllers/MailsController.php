<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 12.05.2016
 * Time: 16:04
 */

namespace yiicms\modules\admin\controllers;

use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yiicms\components\core\Url;
use yiicms\components\core\widgets\Alert;
use yii\web\Controller;
use yiicms\models\core\Mails;
use yiicms\modules\admin\models\mails\MailsSearch;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;

class MailsController extends Controller
{
    const POPUP_MAIL_VIEW = 'mailViewForm';

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
                    'index' => ['POST', 'GET'],
                    'resend' => ['POST'],
                    '*' => [],
                ]
            ]
        ];
    }

    public function actionIndex()
    {
        $this->view->title = \Yii::t('yiicms', 'Отправленные с сайта письма');
        $model = new MailsSearch();
        $dataProvider = $model->search(\Yii::$app->request->queryParams);
        return $this->render('index', ['model' => $model, 'dataProvider' => $dataProvider]);
    }

    public function actionResend($mailId)
    {
        $model = self::findMail($mailId);

        $model->backendId = null;
        $model->sentAt = null;
        if ($model->save()) {
            Alert::success(\Yii::t('yiicms', 'Письмо поставлено в очередь на отправку'));
        } else {
            Alert::error(\Yii::t('yiicms', 'Проблема отправки'));
        }
        return Url::goReturn();
    }

    /**
     * @param int $mailId
     * @return Mails
     * @throws NotFoundHttpException
     */
    private static function findMail($mailId)
    {
        $model = Mails::findOne(['mailId' => $mailId]);
        if ($model === null) {
            throw new NotFoundHttpException;
        }
        return $model;
    }
}
