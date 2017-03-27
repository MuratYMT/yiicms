<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 18.01.2016
 * Time: 8:16
 */

namespace yiicms\components\core\blocks;

use codemix\localeurls\UrlManager;
use yii\web\NotFoundHttpException;
use yii\widgets\ActiveForm;
use yiicms\components\core\Helper;
use yiicms\models\core\Blocks;
use yiicms\models\core\BlocksVisibleForPathInfo;

class BlockEditor extends Blocks
{
    public function init()
    {
        parent::init();
        if (empty($this->contentClass)) {
            $this->contentClass = Helper::rTrimWord(static::class, 'Editor');
        }
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            [['viewFile'], 'in', 'range' => self::availableTemplates($this->contentClass)],
        ]);
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SC_EDIT][] = 'viewFile';
        return $scenarios;
    }

    /**
     * выполняет отрисовку полей редактирования
     * @param ActiveForm $form
     */
    public function renderField($form)
    {
        $position = Blocks::availablePosition();
        $position = array_combine($position, $position);

        $templates = self::availableTemplates($this->contentClass);
        $templates = array_combine($templates, $templates);

        echo $this->renderMultilang($form, 'title');
        echo $form->field($this, 'description')->textInput();
        echo $form->field($this, 'position')->dropDownList($position);
        echo $form->field($this, 'viewFile')->dropDownList($templates);
        echo $form->field($this, 'weight')->textInput();
        echo $form->field($this, 'activy')->checkbox();
        echo $form->field($this, 'pathInfoVisibleOrder')->dropDownList(BlocksVisibleForPathInfo::visibleOrderLabels());

        $this->renderSpecificField($form);
    }

    /**
     * список доступных файлов шаблонов для блока
     * @param string $contentClass класс блока для которого надо выдать список возможных шаблонов
     * @return \string[]
     */
    public static function availableTemplates($contentClass)
    {
        $result = [];
        /** @var BlockWidget $obj */
        $obj = new $contentClass;
        foreach (scandir($obj->viewPath) as $file) {
            if (preg_match('/\.php$/', $file)) {
                $result[] = $file;
            }
        }
        return $result;
    }

    /**
     * выполняет отрисовку полей редактирования специфических для каждого типа блоков
     * должно быть переопределно в дочерних классах
     * @param ActiveForm $form
     */
    public function renderSpecificField($form)
    {
    }

    /**
     * @param int $blockId идентификатор редактируемого блока
     * @param string $lang язык редактирования по умолчанию
     * @return static
     * @throws NotFoundHttpException
     */
    public static function showEdit($blockId, $lang = null)
    {
        $lang = self::checkLang($lang);
        /** @var Blocks $block */
        $block = Blocks::findOne($blockId);

        if ($block === null) {
            throw new NotFoundHttpException(\Yii::t('yiicms', 'Неизвестный блок'));
        }

        /** @var BlockEditor $editorClass */
        $editorClass = $block->contentClass . 'Editor';

        $model = $editorClass::findOne($blockId);
        $model->scenario = self::SC_EDIT;
        $model->lang = $lang;
        return $model;
    }

    /**
     * @param string $contentClass
     * @param string $lang язык редактирования по умолчанию
     * @return static
     */
    public static function showNew($contentClass, $lang = null)
    {
        $lang = self::checkLang($lang);
        $editorClass = $contentClass . 'Editor';
        /** @var self $model */
        $model = new $editorClass;
        $model->lang = $lang;
        $model->scenario = self::SC_EDIT;
        return $model;
    }

    /**
     * @param $blockId
     * @return bool|Blocks
     * @throws NotFoundHttpException
     */
    public static function showDel($blockId)
    {
        $block = Blocks::findOne($blockId);

        if ($block === null) {
            throw new NotFoundHttpException;
        }

        return $block->delete() ? $block : false;
    }

    /**
     * проверяет переданный язык на наличие в системе и если надо устанавливает язык по умолчанию
     * @param string $lang
     * @return string
     * @throws NotFoundHttpException
     */
    private static function checkLang($lang)
    {
        /** @var UrlManager $urlManager */
        $urlManager = \Yii::$app->urlManager;

        if ($lang === null) {
            $lang = \Yii::$app->language;
        } elseif (!in_array($lang, $urlManager->languages, true)) {
            throw new NotFoundHttpException;
        }
        return $lang;
    }
}
