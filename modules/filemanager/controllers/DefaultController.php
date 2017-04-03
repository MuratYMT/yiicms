<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 03.09.2015
 * Time: 8:01
 */

namespace yiicms\modules\filemanager\controllers;

use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yiicms\components\core\Url;
use yiicms\components\core\widgets\Alert;
use yii\web\Controller;
use yiicms\models\core\LoadedFiles;
use yiicms\models\core\VFiles;
use yiicms\models\core\VFolders;
use yiicms\modules\filemanager\models\FileManagerLoadForm;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class DefaultController extends Controller
{
    public $userId;
    public $folderId;

    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'load-files', 'rename-folder', 'delete-folder'],
                        'matchCallback' => function () {
                            if (\Yii::$app->user->isGuest) {
                                return false;
                            }
                            if (empty($folderId = \Yii::$app->request->get('folderId'))) {
                                $folderId = VFolders::userRootFolder($this->userId)->folderId;
                            }
                            $this->folderId = $folderId;
                            return \Yii::$app->user->can('FilesManage', ['folderId' => $folderId]);
                        },
                    ],
                    [
                        'allow' => true,
                        'actions' => ['add-folder'],
                        'matchCallback' => function () {
                            if (\Yii::$app->user->isGuest) {
                                return false;
                            }
                            if (empty($folderId = \Yii::$app->request->get('parentFolderId'))) {
                                $folderId = VFolders::userRootFolder($this->userId)->folderId;
                            }
                            return \Yii::$app->user->can('FilesManage', ['folderId' => $folderId]);
                        },
                    ],
                    [
                        'allow' => true,
                        'actions' => ['rename-file', 'delete-file'],
                        'matchCallback' => function () {
                            if (\Yii::$app->user->isGuest) {
                                return false;
                            }
                            if (empty($fileId = \Yii::$app->request->get('fileId'))) {
                                return false;
                            }
                            return \Yii::$app->user->can('FilesManage', ['fileId' => $fileId]);
                        },
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'add-folder' => ['POST', 'GET'],
                    'index' => ['POST', 'GET'],
                    'rename-file' => ['POST', 'GET'],
                    'rename-folder' => ['POST', 'GET'],
                    'delete-folder' => ['POST'],
                    'delete-file' => ['POST'],
                    'load-files' => ['POST', 'GET'],
                    '*' => [],
                ],
            ],
        ];
    }

    public function beforeAction($action)
    {
        if (empty($userId = \Yii::$app->request->get('userId'))) {
            $userId = \Yii::$app->user->id;
        }
        $this->userId = $userId;
        return parent::beforeAction($action);
    }

    /**
     * @param int $embedded если 1 то шаблон не использовать при отрисовке
     * @return string
     */
    public function actionIndex($embedded = 0)
    {
        $folder = VFolders::findOne(['folderId' => $this->folderId]);

        $this->view->title = \Yii::t('modules/filemanager', 'Файловый менеджер');
        return $this->render('index', ['currentFolder' => $folder, 'embedded' => (int)$embedded]);
    }

    /**
     * загрузка файла
     * @return string
     * @throws \Exception
     * @throws \yii\web\ForbiddenHttpException
     * @throws \yii\web\NotFoundHttpException
     * @throws \yii\base\InvalidParamException
     */
    public function actionLoadFiles()
    {
        $loadForm = new FileManagerLoadForm(['folderId' => $this->folderId]);

        if (\Yii::$app->request->isPost) {
            if ($loadForm->upload()) {
                return json_encode([], JSON_FORCE_OBJECT);
            } else {
                return json_encode(['error' => implode('<hr>', $loadForm->errors['uFiles'])], JSON_FORCE_OBJECT);
            }
        }
        $this->view->title = \Yii::t('modules/filemanager', 'Загрузить файлы');
        return $this->render('load-form', ['currentFolder' => $this->folderId, 'loadModel' => $loadForm]);
    }

    /**
     * переименование каталога
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidParamException
     */
    public function actionRenameFolder()
    {
        $request = \Yii::$app->request;

        /** @var VFolders $model */
        $model = VFolders::findOne($this->folderId);

        if ($request->isPost) {
            //редактирование названия каталога
            $model->scenario = VFolders::SC_RENAME;
            $model->load($request->post());

            if ($model->save()) {
                Alert::success(\Yii::t('modules/filemanager', 'Каталог "{folder}" переименован', ['folder' => $model->title]));
                return Url::goReturn();
            }
        }
        $this->view->title = \Yii::t('modules/filemanager', 'Переименовать каталог "{folder}"', ['folder' => $model->title]);
        return $this->render('rename-folder', ['model' => $model]);
    }

    /**
     * создание нового каталога
     * @param int $parentFolderId
     * @return string
     * @throws \yii\web\ForbiddenHttpException
     * @throws \yii\web\NotFoundHttpException
     * @throws \yii\base\InvalidParamException
     */
    public function actionAddFolder($parentFolderId)
    {
        $request = \Yii::$app->request;

        $model = new VFolders;

        if ($request->isPost) {
            //создание каталога
            $model->scenario = VFolders::SC_INSERT;
            $model->parentId = $parentFolderId;
            $model->userId = $this->userId;

            /** @noinspection NotOptimalIfConditionsInspection */
            if ($model->load($request->post()) && $model->save()) {
                Alert::success(\Yii::t('modules/filemanager', 'Каталог "{folder}" создан', ['folder' => $model->title]));
                return Url::goReturn();
            }
        }
        $this->view->title = \Yii::t('modules/admin', 'Создать каталог');
        return $this->render('rename-folder', ['model' => $model]);
    }

    /**
     * удаление каталога и всего его содержимого
     * @return string
     * @throws \yii\web\ForbiddenHttpException
     * @throws \yii\web\NotFoundHttpException
     * @throws \yii\base\InvalidParamException
     * @throws \yii\db\StaleObjectException
     * @throws \Exception
     */
    public function actionDeleteFolder()
    {
        if (!\Yii::$app->request->isPost) {
            throw new BadRequestHttpException();
        }

        /** @var  VFolders $model */
        $model = VFolders::findOne($this->folderId);

        if (null === ($parentFolderId = $model->parentId)) {
            Alert::error(\Yii::t('modules/filemanager', 'Нельзя удалить корневой каталог'));
            return Url::goReturn();
        }

        if ($model->delete()) {
            Alert::success(\Yii::t('modules/filemanager', 'Каталог удален'));
        } else {
            Alert::error(\Yii::t('modules/filemanager', 'Ошибка удаления каталога'));
        }
        return Url::goReturn();
    }

    public function actionRenameFile($fileId)
    {
        $request = \Yii::$app->request;

        $model = VFiles::findOne(['fileId' => $fileId]);

        if ($request->isPost) {
            //редактирование названия файла
            $loadedFile = $model->loadedFile;
            $loadedFile->scenario = LoadedFiles::SC_RENAME;

            /** @noinspection NotOptimalIfConditionsInspection */
            if ($loadedFile->load($request->post()) && $loadedFile->save()) {
                Alert::success(\Yii::t('modules/filemanager', 'Файл "{file}" переименован', ['file' => $loadedFile->title]));
                return Url::goReturn();
            }
        }
        $this->view->title = \Yii::t('modules/filemanager', 'Переименовать файл "{file}"', ['file' => $model->loadedFile->title]);
        return $this->render('rename-file', ['model' => $model]);
    }

    public function actionDeleteFile($fileId)
    {
        if (!\Yii::$app->request->isPost) {
            throw new BadRequestHttpException();
        }

        $fileModel = VFiles::findOne(['fileId' => $fileId]);

        if ($fileModel->delete()) {
            Alert::success(\Yii::t('modules/filemanager', 'Файл удален'));
        } else {
            Alert::error(\Yii::t('modules/filemanager', 'Ошибка удаления файла'));
        }
        return Url::goReturn();
    }
}
