<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 01.02.2016
 * Time: 15:45
 */

namespace yiicms\modules\users\controllers;

use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\ServerErrorHttpException;
use yiicms\components\core\Url;
use yiicms\components\core\widgets\Alert;
use yiicms\components\YiiCms;
use yiicms\models\core\PmailsIncoming;
use yiicms\models\core\PmailsOutgoing;
use yiicms\modules\admin\models\users\UsersSearch;
use yiicms\modules\users\models\pmails\PmailEdit;
use yiicms\modules\users\models\pmails\PmailsIncomingSearch;
use yiicms\modules\users\models\pmails\PmailsOutgoingSearch;

class PmailsController extends Controller
{
    const FORM_GRID = 'formPmailsGrid';
    const POPUP_PMAIL_USER_SEARCH = 'pmailUserSearchForm';
    const POPUP_PMAIL_READ = 'pmailReadForm';

    /** входящие сообщения */
    const ST_INCOMING = 'incoming';
    /**  отправленные сообщения */
    const ST_SENDED = 'sended';
    /** черновики */
    const ST_DRAFT = 'draft';

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'add' => ['POST', 'GET'],
                    'edit' => ['POST', 'GET'],
                    'forward' => ['POST', 'GET'],
                    'index' => ['POST', 'GET'],
                    'reply' => ['POST', 'GET'],
                    'mark-read' => ['POST'],
                    'mark-unread' => ['POST'],
                    'del' => ['POST'],
                    'send' => ['POST'],
                    '*' => [],
                ]
            ]
        ];
    }

    /**
     * @param string $activeTab
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionIndex($activeTab = null)
    {
        if ($activeTab === null) {
            $activeTab = self::ST_INCOMING;
        }

        if (!in_array($activeTab, [self::ST_INCOMING, self::ST_SENDED, self::ST_DRAFT], true)) {
            throw new NotFoundHttpException;
        }

        $request = \Yii::$app->request;

        if ($activeTab === self::ST_SENDED || $activeTab === self::ST_DRAFT) {
            $model = new PmailsOutgoingSearch();
            $dataProvider = $model->search($request->queryParams, $activeTab === self::ST_DRAFT);
        } else {
            $model = new PmailsIncomingSearch();
            $dataProvider = $model->search($request->queryParams);
        }

        $this->view->title = \Yii::t('modules/users', 'Личные сообщения');
        return $this->render('index', ['dataProvider' => $dataProvider, 'model' => $model, 'activeTab' => $activeTab]);
    }

    /**
     * @param int $rowId ID письма
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     * @internal param int $receiverId получатель письма
     */
    public function actionReply($rowId)
    {
        $request = \Yii::$app->request;
        $model = PmailEdit::showReply($rowId, \Yii::$app->user->id);

        /** @noinspection NotOptimalIfConditionsInspection */
        if ($request->isPost && $model->load($request->post())) {
            if ($request->post('action') === 'send') {
                $model->sended = 1;
            }
            if (YiiCms::$app->pmailService->outgoingPmailSave($model)) {
                return $this->saveSend($model);
            }
        }
        $this->view->title = \Yii::t('modules/users', 'Ответить на личное сообщение');
        return $this->render('pmail-edit', ['model' => $model]);
    }

    /**
     * @param int $rowId ID письма
     * @param int $receiverId
     * @return string|\yii\web\Response
     */
    public function actionForward($rowId, $receiverId = null)
    {
        if ($receiverId === null) {
            return $this->openUserSearch();
        }

        $request = \Yii::$app->request;
        $model = PmailEdit::showForward($rowId, $receiverId);

        /** @noinspection NotOptimalIfConditionsInspection */
        if ($request->isPost && $model->load($request->post())) {
            if ($request->post('action') === 'send') {
                $model->sended = 1;
            }
            if ($model->save()) {
                return $this->saveSend($model);
            }
        }
        $this->view->title = \Yii::t('modules/users', 'Переслать личное сообщение');
        return $this->render('pmail-edit', ['model' => $model]);
    }

    /**
     * @param int $rowId ID письма
     * @return \yii\web\Response
     * @throws BadRequestHttpException
     * @throws ServerErrorHttpException
     */
    public function actionMarkRead($rowId)
    {
        if (!PmailEdit::markMessageRead($rowId, \Yii::$app->user->id)) {
            throw new ServerErrorHttpException;
        }

        Alert::success(\Yii::t('modules/users', 'Сообщение отмечено как прочитанное'));
        return Url::goReturn();
    }

    /**
     * @param int $rowId ID письма
     * @return \yii\web\Response
     * @throws BadRequestHttpException
     * @throws ServerErrorHttpException
     */
    public function actionMarkUnread($rowId)
    {
        if (!PmailEdit::markMessageUnRead($rowId, \Yii::$app->user->id)) {
            throw new ServerErrorHttpException;
        }

        Alert::success(\Yii::t('modules/users', 'Сообщение отмечено как не прочитанное'));
        return Url::goReturn();
    }

    /**
     * @param int $rowId ID письма
     * @return Response
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     * @throws \Exception
     */
    public function actionDel($rowId)
    {
        if (false === PmailEdit::del($rowId, \Yii::$app->user->id)) {
            Alert::error(\Yii::t('modules/users', 'Ошибка удаления'));
        } else {
            Alert::success(\Yii::t('modules/users', 'Сообщение удалено'));
        }
        return Url::goReturn();
    }

    /**
     * @param int $receiverId получатель письма
     * @return string|\yii\web\Response
     */
    public function actionAdd($receiverId = null)
    {
        if ($receiverId === null) {
            return $this->openUserSearch();
        }

        $request = \Yii::$app->request;
        $model = PmailEdit::showNew($receiverId);

        /** @noinspection NotOptimalIfConditionsInspection */
        if ($request->isPost && $model->load($request->post())) {
            if ($request->post('action') === 'send') {
                $model->sended = 1;
            }
            if (YiiCms::$app->pmailService->outgoingPmailSave($model)) {
                return $this->saveSend($model);
            }
        }
        $this->view->title = \Yii::t('modules/users', 'Написать личное сообщение');
        return $this->render('pmail-edit', ['model' => $model]);
    }

    /**
     * @param int $rowId ID письма
     * @return string|\yii\web\Response
     */
    public function actionEdit($rowId)
    {
        $request = \Yii::$app->request;

        $model = PmailEdit::showEdit($rowId, \Yii::$app->user->id);

        /** @noinspection NotOptimalIfConditionsInspection */
        if ($request->isPost && $model->load($request->post())) {
            if ($request->post('action') === 'send') {
                $model->sended = 1;
            }
            if (YiiCms::$app->pmailService->outgoingPmailSave($model)) {
                return $this->saveSend($model);
            }
        }

        $this->view->title = \Yii::t('modules/users', 'Изменить личное сообщение');
        return $this->render('pmail-edit', ['model' => $model]);
    }

    /**
     * @param int $rowId ID письма
     * @return Response
     * @throws BadRequestHttpException
     */
    public function actionSend($rowId)
    {
        /** @var PmailsOutgoing $model */
        $model = PmailEdit::sendMessage($rowId, \Yii::$app->user->id);
        if ($model === false) {
            Alert::error(\Yii::t('yiicms', 'Ошибка отправки сообщения'));
        } else {
            Alert::success(\Yii::t('modules/users', 'Сообщение отправлено'));
        }
        return Url::goReturn();
    }

    /**
     * открывает окно поиска получателя
     */
    private function openUserSearch()
    {
        $this->view->title = \Yii::t('modules/users', 'Выберите получателя');
        $model = new UsersSearch();
        $dataProvider = $model->search(\Yii::$app->request->queryParams, true);

        return $this->render('user-search', ['model' => $model, 'dataProvider' => $dataProvider]);
    }

    /**
     * @param PmailsIncoming|PmailsOutgoing $model
     * @return Response
     * @throws BadRequestHttpException
     */
    private function saveSend($model)
    {
        switch (\Yii::$app->request->post('action')) {
            case 'send':
                Alert::success(\Yii::t('modules/users', 'Сообщение отправлено'));
                return $this->redirect(Url::to(['/pmails', 'activeTab' => self::ST_SENDED]));
            case 'save':
                Alert::success(\Yii::t('modules/users', 'Сообщение сохранено'));
                return $this->redirect(Url::toWithCurrentReturn(['/pmails/edit', 'rowId' => $model->rowId]));
            case 'save-and-close':
                Alert::success(\Yii::t('modules/users', 'Сообщение сохранено'));
                return Url::goReturn();
        }
        throw new BadRequestHttpException();
    }
}
