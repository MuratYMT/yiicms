<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 01.09.2015
 * Time: 8:17
 */

namespace yiicms\modules\admin\controllers;

use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Html;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yiicms\components\core\Helper;
use yiicms\components\core\Url;
use yiicms\components\core\widgets\Alert;
use yiicms\models\content\Category;
use yiicms\models\content\CategoryPermission;
use yiicms\models\content\Page;
use yiicms\models\core\LoadedFiles;
use yiicms\modules\admin\models\pages\LoadImage;
use yiicms\modules\admin\models\pages\PageEdit;
use yiicms\modules\admin\models\pages\PagesSearch;

class PagesController extends Controller
{
    const PANEL_CATEGORIES = 'panelCategoriesCheck';
    const PANEL_LOADED_IMAGES = 'panelLoadedImages';
    const POPUP_LOAD_IMAGE = 'loadFileForPage';
    const FORM_PAGE_EDIT = 'pageEdit';

    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'roles' => ['AdminContent']],
                ],
            ],
            'verb' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'add' => ['POST', 'GET'],
                    'edit' => ['POST', 'GET'],
                    'index' => ['POST', 'GET'],
                    'delete' => ['POST'],
                    'load-images' => ['POST'],
                    '*' => [],
                ]
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel = new PagesSearch();
        $dataProvider = $searchModel->search(\Yii::$app->request->queryParams);

        $this->view->title = \Yii::t('yiicms', 'Страницы сайта');

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionAdd()
    {
        $request = \Yii::$app->request;

        $model = new PageEdit();
        $model->scenario = PageEdit::SC_EDIT;

        $this->view->title = \Yii::t('yiicms', 'Создать страницу');

        if ($request->isPost) {
            /** @noinspection NotOptimalIfConditionsInspection */
            if ($model->load($request->post()) && $model->save()) {
                Alert::success(\Yii::t('yiicms', 'Страница создана'));
                if ('save-and-close' === $request->post('action')) {
                    $url = Url::to(['/admin/pages']);
                } else {
                    $url = Url::toWithCurrentReturn(['/admin/pages/edit', 'pageId' => $model->pageId]);
                }
                return $this->redirect($url);
            } else {
                Helper::errorModel($model);
            }
        }

        return $this->render('edit', [
            'model' => $model,
            'loadModel' => new LoadImage(),
            'categories' => Category::available(null, CategoryPermission::PAGE_ADD)
        ]);
    }

    public function actionEdit($pageId)
    {
        $request = \Yii::$app->request;

        if (null === ($model = PageEdit::findOne($pageId))) {
            throw new NotFoundHttpException;
        }
        $model->scenario = PageEdit::SC_EDIT;

        /** @noinspection NotOptimalIfConditionsInspection */
        if ($request->isPost) {
            if ($model->load($request->post()) && $model->save()) {
                Alert::success(\Yii::t('yiicms', 'Страница изменена'));
                if ('save-and-close' === $request->post('action')) {
                    return Url::goReturn();
                }
            } else {
                Helper::errorModel($model);
            }
        }
        $this->view->title = \Yii::t('yiicms', 'Изменить страницу');

        return $this->render('edit', [
            'model' => $model,
            'loadModel' => new LoadImage(),
            'categories' => Category::available(null, CategoryPermission::PAGE_ADD, $model->categories)
        ]);
    }

    public function actionDelete($pageId)
    {
        if (null === ($model = Page::findOne($pageId))) {
            throw new NotFoundHttpException;
        }
        if ($model->delete()) {
            Alert::success(\Yii::t('yiicms', 'Страница "{title}" Удалена', ['title' => $model->title]));
        } else {
            Alert::error(\Yii::t('yiicms', 'Ошибка удаления страницы'));
        }
        return Url::goReturn();
    }

    public function actionLoadImages()
    {
        $loadForm = new LoadImage();
        if (false !== ($file = $loadForm->upload())) {
            $file = reset($file);
            /** @var LoadedFiles $file */
            $thumb = $file->file->asThumbnail($this->view, 128, 128);
            $link = $file->file->asPhotoUrl($this->view);
            return json_encode([
                'image' => [
                    'id' => [PagesController::FORM_PAGE_EDIT => [Html::getInputName(new PageEdit(), 'imagesIds') . '[]' => $file->id]],
                    'path' => $file->path,
                    'imageid' => $file->id,
                    'title' => $file->title,
                    'link' => $link,
                    'thumb' => $thumb,
                ],
            ], JSON_FORCE_OBJECT);
        } else {
            return json_encode(['error' => implode('<hr>', $loadForm->errors['uFiles'])]);
        }
    }
}
