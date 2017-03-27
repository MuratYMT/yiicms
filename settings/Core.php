<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 29.06.2015
 * Time: 9:35
 */

namespace yiicms\settings;

use yii\widgets\ActiveForm;
use yiicms\components\admin\SettingsGroup;
use yiicms\components\core\SettingsBlock;
use yiicms\components\core\validators\StringArrayValidator;
use yiicms\components\core\yii\Theme;
use yiicms\themes\base\BaseTheme;

class Core extends SettingsBlock
{
    /**
     * @inheritdoc
     */
    public function getSettings()
    {
        return [
            'main' => [
                'adminMail' => [
                    'title' => \Yii::t('modules/admin', 'Адрес E-mail администратора сайта'),
                    'value' => 'admin@is-r.org',
                    'rules' => [
                        ['string', 'max' => 200],
                        ['email'],
                    ],
                ],
                'robotMail' => [
                    'title' => \Yii::t('modules/admin', 'Адрес E-mail используемый для автоматической рассылки сообщений с сайта'),
                    'value' => 'robot@is-r.org',
                    'rules' => [
                        ['string', 'max' => 200],
                        ['email'],
                    ],
                ],
                'siteName' => [
                    'title' => \Yii::t('modules/admin', 'Название сайта'),
                    'value' => 'Yii 2 CMS',
                    'rules' => [
                        ['string', 'max' => 200],
                    ],
                ],
                'theme' => [
                    'title' => \Yii::t('modules/admin', 'Тема оформления'),
                    'value' => BaseTheme::className(),
                    'rules' => [
                        ['string', 'max' => 200],
                        ['in', 'range' => Theme::availableThemes()],
                    ],
                ],
            ],
            'speed' => [
                'uploadStructureDepth' => [
                    'title' => \Yii::t('modules/admin', 'Количество уровней вложенности папки upload'),
                    'value' => 2,
                    'rules' => [
                        ['integer'],
                        ['in', 'range' => [1, 2, 3]],
                    ],
                    'extra' => [
                        1 => \Yii::t('modules/admin', '1 (64 папки)'),
                        2 => \Yii::t('modules/admin', '2 (4096 папки)'),
                        3 => \Yii::t('modules/admin', '3 (262144 папки)'),
                    ],
                ],
                'cacheDuration' => [
                    'title' => \Yii::t('modules/admin', 'Максимальный срок жизни кеша'),
                    'value' => 3600,
                    'rules' => [
                        ['integer', 'min' => 0],
                    ],
                ],
            ],
            'filemanager' => [
                'imageFileExtension' => [
                    'title' => \Yii::t('modules/admin', 'Расширения файлов изображений доступных к загрузке'),
                    'value' => ['jpg', 'jpeg', 'gif', 'png'],
                    'rules' => [
                        [StringArrayValidator::className(), 'max' => 10, 'min' => 1],
                    ],
                ],
                'fileExtension' => [
                    'title' => \Yii::t('modules/admin', 'Расширения файлов доступных к загрузке'),
                    'value' => ['doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'swf', 'zip', 'rar', 'rtf', 'pdf', 'psd', 'mp3', 'wma'],
                    'rules' => [
                        [StringArrayValidator::className(), 'max' => 10, 'min' => 1],
                    ],
                ],
                'maxFileSize' => [
                    'title' => \Yii::t('modules/admin', 'Максимальный размер загружаемых файлов'),
                    'value' => 16777216,
                    'rules' => [
                        ['integer', 'min' => 0],
                    ],
                ],
                'minPhotoSize' => [
                    'title' => \Yii::t('modules/admin', 'Минимальный размер картинки по высоте или ширине'),
                    'value' => 64,
                    'rules' => [
                        ['integer', 'min' => 0],
                    ],
                ],
            ],
        ];
    }

    public function getSettingsGroupTitle($name = null)
    {

        $titles = [
            'main' => \Yii::t('modules/admin', 'Основные'),
            //'security' => \Yii::t('modules/admin', 'Безопасность'),
            'filemanager' => \Yii::t('modules/admin', 'Загрузка файлов'),
            'speed' => \Yii::t('modules/admin', 'Быстродействие'),
        ];

        return $name === null ? $titles : $titles[$name];
    }

    public function renderMainSettings(ActiveForm $form, SettingsGroup $model)
    {
        ob_start();

        echo $form->field($model, 'siteName')->textInput();
        echo $form->field($model, 'adminMail')->textInput();
        echo $form->field($model, 'robotMail')->textInput();

        $themItems = [];
        /** @var Theme[] $themes */
        $themes = Theme::availableThemes();
        foreach ($themes as $theme){
            /** @noinspection PhpIllegalArrayKeyTypeInspection */
            $themItems[$theme] = $theme::themeTitle();
        }

        echo $form->field($model, 'theme')->dropDownList($themItems);

        return ob_get_clean();
    }

    public function renderFilemanagerSettings(ActiveForm $form, SettingsGroup $model)
    {
        ob_start();

        echo $form->field($model, 'imageFileExtension')->textInput();
        echo $form->field($model, 'fileExtension')->textInput();
        echo $form->field($model, 'maxFileSize')->textInput();
        echo $form->field($model, 'minPhotoSize')->textInput();

        return ob_get_clean();
    }

    public function renderSpeedSettings(ActiveForm $form, SettingsGroup $model)
    {
        ob_start();

        $settings = $this->getSettings();

        echo $form->field($model, 'uploadStructureDepth')->dropDownList($settings['speed']['uploadStructureDepth']['extra']);
        echo $form->field($model, 'cacheDuration')->textInput();

        return ob_get_clean();
    }
}
