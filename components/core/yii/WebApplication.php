<?php
/**
 * Created by PhpStorm.
 * User: murat
 * Date: 03.07.2015
 * Time: 22:22
 */

namespace yiicms\components\core\yii;

use codemix\localeurls\UrlManager;
use yiicms\models\core\LoadedFiles;
use yii\web\Application;
use yiicms\models\core\Settings;

class WebApplication extends Application
{
    use CommonApplicationTrait;

    /**
     * @inheritdoc
     * @throws \yii\base\InvalidParamException
     */
    protected function bootstrap()
    {
        parent::bootstrap();
        \Yii::setAlias('@upload', '@webroot/' . $this->uploadFolder);    //путь на диске до публичной папки загрузки
        \Yii::setAlias('@webupload', '@web/' . $this->uploadFolder);     //URL до папки загрузки
        $this->registerUloadFolder();
    }

    public function init()
    {
        parent::init();
        $themeClass = Settings::get('core.theme');
        $this->view->theme = new $themeClass;
    }

    /**
     * @inheritDoc
     * @throws \yii\web\NotFoundHttpException
     */
    public function handleRequest($request)
    {
        /** @var UrlManager $urlManager */
        $urlManager = $this->urlManager;
        //добавление обработки превьюх
        $urlManager->addRules([
            $this->uploadFolder . '/<path:([\w|\-]/)+><fileName:[\w|\-|\.]+>_<width:\d+>' . LoadedFiles::SIZE_DELIMITER . '<height:\d+>_<style:\w+>.\w+' => 'site/thumbnail',
        ]);
        //обработка загруженных файлов
        $urlManager->addRules([
            $this->uploadFolder . '/<path:([\w|\-]/)+><fileName:[\w|\-|\.]+>' => 'site/image',
        ]);

        $urlManager->ignoreLanguageUrlPatterns = [
            '#^' . $this->uploadFolder . '/.+#' => '#^' . $this->uploadFolder . '/.+#',
        ];
        return parent::handleRequest($request);
    }
}
