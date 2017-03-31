<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 20.02.2017
 * Time: 16:57
 */

namespace yiicms\controllers;

use yii\captcha\CaptchaAction;
use yii\filters\AccessControl;
use yii\web\ErrorAction;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\Controller;
use yiicms\models\content\Page;
use yiicms\models\core\LoadedFiles;
use yiicms\models\core\Settings;

class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => ErrorAction::className(),
            ],
            'captcha' => [
                'class' => CaptchaAction::className(),
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    public function actionIndex()
    {
        if (null !== ($pageSlug = Settings::get('core.firstPage'))) {
            $page = Page::findBySlug($pageSlug);
            if ($page !== null) {
                $this->layout = '@theme/views/layouts/first-page-layout';
                return $this->renderContent($page->pageText);
            }
        }
        $this->view->title = Settings::get('core.siteName');
        return $this->render('index');
    }

    /**
     * формирование предпросмотров картинок указанного размера
     * @param string $path путь до папки с файлом
     * @param string $fileName имя файла картинки для которого надо сформировать предпросмотр
     * @param int $width максимальная ширина предпросмотра
     * @param int $height максимальная высота предпросмотра
     * @param string $style стиль отображения (уместить в размеры, обрезать в размер)
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionThumbnail($path, $fileName, $width, $height, $style)
    {
        $thumbnailFile = LoadedFiles::publishThumbnail($path, $fileName, $width, $height, $style);
        if ($thumbnailFile === false) {
            throw new NotFoundHttpException;
        } elseif ($thumbnailFile === -1) {
            throw new ServerErrorHttpException;
        }
        \Yii::$app->response->sendFile($thumbnailFile, null, ['inline' => true]);
    }

    /**
     * копирование файлов в публичную папку
     * @param string $path путь до папки с файлом
     * @param string $fileName имя файла
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionImage($path, $fileName)
    {
        $imageFile = LoadedFiles::publishFile($path, $fileName);
        if ($imageFile === false) {
            throw new NotFoundHttpException;
        } elseif ($imageFile === -1) {
            throw new ServerErrorHttpException;
        }
        \Yii::$app->response->sendFile($imageFile, null, ['inline' => true]);
    }
}