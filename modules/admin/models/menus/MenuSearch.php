<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 22.01.2016
 * Time: 10:05
 */

namespace yiicms\modules\admin\models\menus;

use yiicms\components\core\TreeHelper;
use yiicms\models\core\Menus;
use yii\base\Model;
use yii\data\ArrayDataProvider;

class MenuSearch extends Model
{
    public $title;
    public $subTitle;
    public $weight;
    public $pathInfoVisibleOrder;

    public function rules()
    {
        return [
            [['title', 'subTitle'], 'string', 'max' => 64],
            [['weight', 'pathInfoVisibleOrder'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'link' => \Yii::t('yiicms', 'Ссылка'),
            'weight' => \Yii::t('yiicms', 'Вес'),
            'pathInfoVisibleOrder' => \Yii::t('yiicms', 'Порядок применения прав видимости'),
            'title' => \Yii::t('yiicms', 'Заголовок'),
            'subTitle' => \Yii::t('yiicms', 'Подзаголовок'),
        ];
    }

    public function search($params)
    {
        $menus = TreeHelper::build(Menus::find()->all(), 'menuId', 'weight');

        if ($this->load($params) && $this->validate()) {
            $this->filter($menus);
        }

        return new ArrayDataProvider([
            'allModels' => $menus
        ]);
    }

    /**
     * @param array $menus
     */
    protected function filter(&$menus)
    {
        if (!empty($this->title)) {
            foreach ($menus as $key => $menu) {
                if (mb_stripos($menu['title'], $this->title) === false) {
                    unset($menus[$key]);
                }
            }
        }

        if (!empty($this->subTitle)) {
            foreach ($menus as $key => $menu) {
                if (mb_stripos($menu['subTitle'], $this->subTitle) === false) {
                    unset($menus[$key]);
                }
            }
        }

        if (!empty($this->weight)) {
            foreach ($menus as $key => $menu) {
                if (mb_stripos($menu['weight'], $this->weight) === false) {
                    unset($menus[$key]);
                }
            }
        }

        if (!empty($this->pathInfoVisibleOrder)) {
            foreach ($menus as $key => $menu) {
                if (mb_stripos($menu['pathInfoVisibleOrder'], $this->pathInfoVisibleOrder) === false) {
                    unset($menus[$key]);
                }
            }
        }
    }
}
