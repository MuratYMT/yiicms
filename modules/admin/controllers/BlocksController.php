<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 13.01.2016
 * Time: 9:21
 */

namespace yiicms\modules\admin\controllers;

use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yiicms\components\core\Url;
use yiicms\modules\admin\models\blocks\BlocksSearch;
use yiicms\modules\admin\models\blocks\BlocksVisibleForPathInfoSearch;
use yiicms\modules\admin\models\blocks\BlocksVisibleForRoleSearch;
use yiicms\components\core\blocks\BlockEditor;
use yiicms\components\core\widgets\Alert;
use yii\web\Controller;
use yiicms\models\core\Blocks;
use yiicms\models\core\BlocksVisibleForPathInfo;
use yiicms\models\core\Settings;

class BlocksController extends Controller
{
    const FORM_BLOCK_EDIT = 'block-edit-form';

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
            'verb' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'add' => ['POST', 'GET'],
                    'edit' => ['POST', 'GET'],
                    'index' => ['POST', 'GET'],
                    'path-info-visible' => ['POST', 'GET'],
                    'path-info-visible-add' => ['POST', 'GET'],
                    'path-info-visible-edit' => ['POST', 'GET'],
                    'role-visible' => ['POST', 'GET'],
                    'del-block' => ['POST'],
                    'role-visible-grant' => ['POST'],
                    'role-visible-revoke' => ['POST'],
                    'path-info-visible-del' => ['POST'],
                    '*' => [],
                ]
            ]
        ];
    }

    /**
     * страница блоков
     */
    public function actionIndex()
    {
        $request = \Yii::$app->request;

        $model = new BlocksSearch();

        $dataProvider = $model->search($request->queryParams);

        $this->view->title = \Yii::t('yiicms', 'Блоки');
        return $this->render('index', ['dataProvider' => $dataProvider, 'model' => $model]);
    }

    public function actionEdit($blockId, $lang = null)
    {
        $request = \Yii::$app->request;

        $model = BlockEditor::showEdit($blockId, $lang);
        /** @noinspection NotOptimalIfConditionsInspection */
        if ($request->isPost && $model->load($request->post()) && $model->save()) {
            Alert::success(\Yii::t('yiicms', 'Блок "{block}" отредактирован', ['block' => $model->title]));
            return Url::goReturn();
        }

        $this->view->title = \Yii::t('yiicms', 'Изменить блок "{block}"', ['block' => $model->title]);
        return $this->render('block-edit', ['model' => $model]);
    }

    public function actionAdd($contentClass, $lang = null)
    {
        $request = \Yii::$app->request;

        if (!in_array($contentClass, Blocks::getAvailableBlocksClass(), true)) {
            throw new NotFoundHttpException;
        }

        $model = BlockEditor::showNew($contentClass, $lang);
        /** @noinspection NotOptimalIfConditionsInspection */
        if ($request->isPost && $model->load($request->post()) && $model->save()) {
            Alert::success(\Yii::t('yiicms', 'Блок "{block}" создан', ['block' => $model->title]));
            return Url::goReturn();
        }
        $this->view->title = \Yii::t('yiicms', 'Создать блок');
        return $this->render('block-edit', ['model' => $model]);
    }

    public function actionDelBlock($blockId)
    {
        $block = BlockEditor::showDel($blockId);

        if ($block !== false) {
            Alert::success(\Yii::t('yiicms', 'Блок "{block}" удален', ['block' => $block->title]));
        } else {
            Alert::error(\Yii::t('yiicms', 'Ошибка удаления блока'));
        }
        return Url::goReturn();
    }

    public function actionRoleVisible($blockId)
    {
        $request = \Yii::$app->request;

        $block = self::getBlock($blockId);

        $model = new BlocksVisibleForRoleSearch();
        $dataProvider = $model->search($block, $request->queryParams);

        $this->view->title = \Yii::t('yiicms', 'Роли для которых виден блок "{block}"', ['block' => $block->title]);
        return $this->render('role-permission', ['dataProvider' => $dataProvider, 'blockId' => $block->blockId, 'model' => $model]);
    }

    public function actionRoleVisiableGrant($blockId, $roleName)
    {
        $block = self::checkBlockAndRole($blockId, $roleName);
        $block->grant($roleName);

        Alert::success(\Yii::t('yiicms', 'Видимость блока для роли "{role}" предоставлена', ['role' => $roleName]));
        return $this->actionRoleVisible($blockId);
    }

    public function actionRoleVisiableRevoke($blockId, $roleName)
    {
        $block = self::checkBlockAndRole($blockId, $roleName);
        $block->revoke($roleName);

        Alert::success(\Yii::t('yiicms', 'Видимость блока для роли "{role}" отменена', ['role' => $roleName]));
        return $this->actionRoleVisible($blockId);
    }

    public function actionPathInfoVisible($blockId)
    {
        $block = self::getBlock($blockId);

        $model = new BlocksVisibleForPathInfoSearch();
        $dataProvider = $model->search($blockId);

        $this->view->title = \Yii::t('yiicms', 'Правила видимости блока "{block}" на страницах сайта', ['block' => $block->title]);
        return $this->render('pathinfo-permission', ['dataProvider' => $dataProvider, 'blockId' => $block->blockId, 'model' => $model]);
    }

    public function actionPathInfoVisibleAdd($blockId)
    {
        $request = \Yii::$app->request;

        $block = self::getBlock($blockId);
        $model = new BlocksVisibleForPathInfo(['blockId' => $blockId]);
        /** @noinspection NotOptimalIfConditionsInspection */
        if ($request->isPost && $model->load($request->post()) && $model->save()) {
            Alert::success(\Yii::t('yiicms', 'Правило добавлено'));
            return Url::goReturn();
        }

        $this->view->title = \Yii::t('yiicms', 'Добавить правило видимости блока "{block}" на страницах сайта', ['block' => $block->title]);
        return $this->render('pathinfo-visible-edit', ['model' => $model]);
    }

    public function actionPathInfoVisibleEdit($permId)
    {
        $request = \Yii::$app->request;
        /** @var BlocksVisibleForPathInfo $model */
        $model = BlocksVisibleForPathInfo::findOne($permId);

        if ($model === null) {
            throw new NotFoundHttpException;
        }

        $block = self::getBlock($model->blockId);
        /** @noinspection NotOptimalIfConditionsInspection */
        if ($request->isPost && $model->load($request->post()) && $model->save()) {
            Alert::success(\Yii::t('yiicms', 'Правило изменено'));
            return Url::goReturn();
        }

        $this->view->title = \Yii::t('yiicms', 'Изменить правило видимости блока "{block}" на страницах сайта', ['block' => $block->title]);
        return $this->render('pathinfo-visible-edit', ['model' => $model]);
    }

    public function actionPathInfoVisibleDel($permId)
    {
        /** @var BlocksVisibleForPathInfo $model */
        $model = BlocksVisibleForPathInfo::findOne($permId);

        if ($model === null) {
            throw new NotFoundHttpException;
        }

        $block = self::getBlock($model->blockId);

        if ($model->delete()) {
            Alert::success(\Yii::t('yiicms', 'Правило видимости для блока "{block}" удалено', ['block' => $block->title]));
        } else {
            Alert::error(\Yii::t('yiicms', 'Ошибка удаления правила'));
        }
        return Url::goReturn();
    }

    /**
     * @param int $blockId
     * @return Blocks
     * @throws NotFoundHttpException
     */
    private static function getBlock($blockId)
    {
        if (null === ($block = Blocks::findOne($blockId))) {
            throw new NotFoundHttpException;
        }

        return $block;
    }

    /**
     * @param $blockId
     * @param $roleName
     * @return Blocks
     * @throws NotFoundHttpException
     */
    private static function checkBlockAndRole($blockId, $roleName)
    {
        if ($roleName !== Settings::get('users.defaultGuestRole') && null === \Yii::$app->authManager->getRole($roleName)) {
            throw new NotFoundHttpException;
        }

        return self::getBlock($blockId);
    }
}
