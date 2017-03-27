<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 17.08.2015
 * Time: 16:20
 */

namespace yiicms\modules\admin\controllers;

use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yiicms\components\core\Url;
use yiicms\models\content\CategoryPermission;
use yiicms\modules\admin\models\categories\CategoriesSearch;
use yiicms\modules\admin\models\categories\CategoryEditForm;
use yiicms\modules\admin\models\categories\CategoryPermissionEditForm;
use yiicms\modules\admin\models\categories\CategoryPermissionSearch;
use yiicms\components\core\widgets\Alert;
use yii\web\Controller;
use yiicms\models\content\Category;
use yiicms\models\core\Settings;

class CategoriesController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['AdminContent'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'add' => ['POST', 'GET'],
                    'edit' => ['POST', 'GET'],
                    'index' => ['POST', 'GET'],
                    'permission' => ['POST', 'GET'],
                    'permission-edit' => ['POST', 'GET'],
                    'delete' => ['POST'],
                    '*' => [],
                ]
            ]
        ];
    }

    public function actionIndex()
    {
        $request = \Yii::$app->request;
        $model = new CategoriesSearch();
        $dataProvider = $model->search($request->queryParams);

        $this->view->title = \Yii::t('yiicms', 'Категории');

        return $this->render('index', ['dataProvider' => $dataProvider, 'model' => $model]);
    }

    /**
     * @param int $parentId
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidParamException
     */
    public function actionAdd($parentId = 0)
    {
        $request = \Yii::$app->request;
        $model = CategoryEditForm::showNew($parentId);
        /** @noinspection NotOptimalIfConditionsInspection */
        if ($request->isPost && $model->load($request->post()) && $model->save()) {
            Alert::success(\Yii::t('yiicms', 'Категория создана'));
            return Url::goReturn();
        }
        $this->view->title = \Yii::t('yiicms', 'Добавить категорию');
        return $this->render('category-edit', ['model' => $model]);
    }

    public function actionEdit($categoryId)
    {
        $request = \Yii::$app->request;
        $model = CategoryEditForm::showEdit($categoryId);
        /** @noinspection NotOptimalIfConditionsInspection */
        if ($request->isPost && $model->load($request->post()) && $model->save()) {
            Alert::success(\Yii::t('yiicms', 'Категория "{category}" изменена', ['category' => $model->title]));
            return Url::goReturn();
        }
        $this->view->title = \Yii::t('yiicms', 'Изменить категорию "{category}"', ['category' => $model->title]);
        return $this->render('category-edit', ['model' => $model]);
    }

    public function actionDelete($categoryId, $removeChild)
    {
        $model = self::getCategory($categoryId);

        if ((int)$removeChild === 1) {
            $res = $model->deleteRecursive();
        } else {
            $res = $model->delete();
        }

        if ($res) {
            Alert::success(\Yii::t('yiicms', 'Категория "{category}" удалена', ['category' => $model->title]));
        } else {
            Alert::error(\Yii::t('yiicms', 'Ошибка удаления категории'));
        }
        return Url::goReturn();
    }

    public function actionPermission($categoryId)
    {
        $category = self::getCategory($categoryId);

        $model = new CategoryPermissionSearch();
        $dataProvider = $model->search($category, \Yii::$app->request->queryParams);

        $this->view->title = \Yii::t('yiicms', 'Разрешения категории "{category}"', ['category' => $category->title]);

        return $this->render('permission', ['dataProvider' => $dataProvider, 'categoryId' => $category->categoryId, 'model' => $model]);
    }

    public function actionPermissionEdit($categoryId, $roleName)
    {
        if ($roleName !== Settings::get('users.defaultGuestRole') && null === \Yii::$app->authManager->getRole($roleName)) {
            throw new NotFoundHttpException();
        }

        $request = \Yii::$app->request;
        $category = self::getCategory($categoryId);
        $model = CategoryPermissionEditForm::create($category, $roleName);
        /** @noinspection NotOptimalIfConditionsInspection */
        if ($request->isPost && $model->load($request->post()) && $model->save()) {
            Alert::success(\Yii::t('yiicms', 'Разрешения изменены'));
            return Url::goReturn();
        }

        $this->view->title = \Yii::t(
            'yiicms',
            'Редактировать разрешения категории "{category}" для роли "{role}"',
            ['category' => $category->title, 'role' => $roleName]
        );
        return $this->render('permission-edit', ['model' => $model]);
    }

    /**
     * ищет указанную категорию
     * @param int $id идентифкатор категории
     * @return null|Category
     * @throws NotFoundHttpException
     */
    protected static function getCategory($id)
    {
        if (null !== ($model = Category::findOne($id))) {
            return $model;
        }
        throw new NotFoundHttpException();
    }
}
