<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 24.05.2016
 * Time: 14:56
 */

namespace yiicms\components\filemanager;

use yii\bootstrap\Dropdown;
use yii\helpers\Html;
use yiicms\components\core\Url;
use yiicms\models\core\VFolders;

class FolderDropDown extends Dropdown
{
    /** @var VFolders */
    public $folder;

    public $embedded = 0;

    /**
     * @inheritDoc
     */
    public function run()
    {
        $folders = VFolders::allFolders($this->folder->userId);
        $this->items = [];

        foreach ($folders as $folder) {
            if ($folder->levelNod === 1) {
                $label = $folder->title;
            } else {
                $label = str_repeat('&nbsp;', ($folder->levelNod - 1) * 6) . '|--' . $folder->title;
            }
            if ($folder->folderId === $this->folder->folderId) {
                $label = '<span class="fa fa-check"> </span>' . $label;
            } else {
                $label = '<span class="fa fa-circle-o"> </span>' . $label;
            }
            $this->items[$folder->folderId] = [
                'encode' => false,
                'label' => $label,
                'url' => Url::to(['/filemanager', 'folderId' => $folder->folderId, 'embedded' => $this->embedded]),
                'linkOptions' => ['data-pjax' => 1],
            ];
        }

        Html::addCssStyle($this->options, ['width' => '100%']);

        return parent::run();
    }
}
