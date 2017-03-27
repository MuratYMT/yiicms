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

class CommentValidator extends FilterValidator
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
                    'b',
                    'strong',
                    'sub',
                    'sup',
                    'ul',
                    'ol',
                    'li',
                    'a',
                    'img',
                    'hr',
                    'br',
                    'span',
                    'p'
                ]);
                $config->set('HTML.AllowedAttributes', [
                    'a.title',
                    'a.href',
                    'a.name',
                    'a.id',
                    'a.rel',
                    'span.style',
                    'p.style',
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
