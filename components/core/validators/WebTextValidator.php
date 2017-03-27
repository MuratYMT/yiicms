<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 29.09.2015
 * Time: 15:46
 */

namespace yiicms\components\core\validators;

use yii\helpers\HtmlPurifier;
use yii\validators\FilterValidator;

class WebTextValidator extends FilterValidator
{
    /**
     * @inheritDoc
     */
    public function init()
    {
        $this->filter = function ($value) {
            return HtmlPurifier::process($value, function ($config) {
                /** @var $config \HTMLPurifier_Config */
                $config->set('HTML.AllowedElements', [
                    'pre',
                    'i',
                    'em',
                    'h1',
                    'h2',
                    'h3',
                    'h4',
                    'h5',
                    'h6',
                    'b',
                    'strong',
                    'sub',
                    'sup',
                    'ul',
                    'ol',
                    'li',
                    'div',
                    'a',
                    'img',
                    'hr',
                    'br',
                    'span',
                    'p',
                    'table',
                    'tr',
                    'td',
                    'tbody',
                    'thead'
                ]);
                $config->set('HTML.AllowedAttributes', [
                    'a.title',
                    'a.href',
                    'a.name',
                    'a.id',
                    'a.rel',
                    'div.style',
                    'span.style',
                    'p.style',
                    'table.style',
                    'table.summary',
                    'table.border',
                    'table.cellpadding',
                    'table.cellspacing',
                    'h1.style',
                    'h2.style',
                    'h3.style',
                    'h4.style',
                    'h5.style',
                    'h6.style',
                    'td.rowspan',
                    'td.colspan',
                    'td.abbr',
                    'img.style',
                    'img.src',
                    'img.title',
                    'img.alt',
                ]);
                $config->set('HTML.Nofollow', true);
                $config->set('HTML.Doctype', 'HTML 4.01 Strict');

                $config->set('AutoFormat.AutoParagraph', true); // авто добавление <p> в тексте при переносе
                $config->set('AutoFormat.RemoveEmpty', true); // удаляет пустые теги, есть исключения*

                $def = $config->getHTMLDefinition(true);
                $def->addAttribute('img', 'attr', new \HTMLPurifier_AttrDef_Text());
                $def->addAttribute('img', 'align', new \HTMLPurifier_AttrDef_Enum(['right', 'left', 'center']));
                $def->addAttribute('img', 'width', new \HTMLPurifier_AttrDef_Integer(false, false));
                $def->addAttribute('img', 'height', new \HTMLPurifier_AttrDef_Integer(false, false));
                $def->addAttribute('img', 'hspace', new \HTMLPurifier_AttrDef_Integer());
                $def->addAttribute('img', 'vspace', new \HTMLPurifier_AttrDef_Integer());
            });
        };
        parent::init();
    }
}
