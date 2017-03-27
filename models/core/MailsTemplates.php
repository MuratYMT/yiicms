<?php

namespace yiicms\models\core;

use yii\db\ActiveRecord;
use yiicms\components\core\behavior\MultiLangBehavior2;
use yiicms\components\core\websun;

/**
 * This is the model class for table "web.mailTemplates".
 * @property string $templateId Template ID
 * @property string $description Описание шаблона
 * @property string[] $templateM Текст шаблона письма на разных языках
 * @property string $template Текст шаблона письма на языке по умолчанию
 * @property string[] $subjectM Шаблон заголовка письма на разных языках
 * @property string $subject Шаблон заголовка письма на языке по умолчанию
 * @property string $lang с каким языком по умолчанию из языкового массива на котором представлен объект должен работать объект
 * @method attributeRulesLang() @see MultiLangBehavior2::attributeRulesLang()
 * @method attributeLabelsLang() @see MultiLangBehavior2::attributeLabelsLang()
 * @method string renderMultilang($activeForm, $attribute) @see MultiLangBehavior2::renderMultilang()
 */
class MailsTemplates extends ActiveRecord
{
    const SC_EDIT = 'edit';

    public $params = [];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%mailTemplates}}';
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            [
                'class' => MultiLangBehavior2::class,
                'attributes' => [
                    'template' => [
                        [
                            ['string', 'max' => 64000]
                        ],
                        \Yii::t('yiicms', 'Шаблон письма')
                    ],
                    'subject' => [
                        [
                            ['string', 'max' => 256]
                        ],
                        \Yii::t('yiicms', 'Шаблон заголовка')
                    ]
                ],
                'trgmIndex' => false,
            ],
        ]);
    }

    public function scenarios()
    {
        return [
            self::SCENARIO_DEFAULT => [],
            self::SC_EDIT => array_merge(array_keys($this->attributeLabelsLang()), ['subject', 'template']),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge($this->attributeRulesLang(), [
            [['description', 'template', 'subject'], 'required'],
            [['templateId'], 'integer'],
            [['template'], 'string', 'max' => 64000],
            [['subject'], 'string', 'max' => 256],
            [['templateId'], 'string', 'max' => 40],
            [['description'], 'string', 'max' => 256],
            [['templateM', 'subjectM'], 'safe'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge($this->attributeLabelsLang(), [
            'templateId' => \Yii::t('yiicms', 'Template ID'),
            'description' => \Yii::t('yiicms', 'Описание шаблона'),
            'template' => \Yii::t('yiicms', 'Шаблон письма'),
            'subject' => \Yii::t('yiicms', 'Шаблон заголовка'),
        ]);
    }

    private function renderString($attributeName)
    {
        // функция-обёртка для быстрого вызова класса
        // принимает шаблон непосредственно в виде кода
        $W = new websun([
            'data' => $this->params,
            'templates_root' => self::templateRootDir(),
            'no_global_vars' => false,
        ]);
        return $W->parse_template($this->$attributeName);
    }

    public function renderSubject()
    {
        return $this->renderString('subject');
    }

    public function renderTemplate()
    {
        return $this->renderString('template');
    }

    /**
     * поиск шаблона
     * @param string $templateId ID шаблона
     * @return MailsTemplates|null null если такой шаблон не существует
     */
    public static function findTemplate($templateId)
    {
        $model = self::findOne(['templateId' => $templateId]);
        if ($model === null) {
            $template = self::loadDefaultTemplate($templateId);
            if (!is_array($template)) {
                return null;
            }

            $model = new self;
            $model->templateId = $templateId;
            $model->templateM = $template['template'];
            $model->subjectM = $template['subject'];
        }

        return $model;
    }

    /**
     * загружает шаблон по умолчанию
     * @param string $templateId
     * @return array|false
     */
    private static function loadDefaultTemplate($templateId)
    {
        /** @var array $template */
        $file = self::templateRootDir() . DIRECTORY_SEPARATOR . $templateId . '.php';
        if (!file_exists($file) || !is_file($file)) {
            return false;
        }
        /** @noinspection PhpIncludeInspection */
        include $file;
        return $template;
    }

    private static function templateRootDir()
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'mailTemplates';
    }
}
