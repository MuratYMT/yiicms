<?php
/**
 * Created by PhpStorm.
 * User: murat
 * Date: 03.09.2017
 * Time: 22:32
 */

namespace yiicms\models\core\constants;

class VisibleForPathInfoConst
{
    /** запретить везде потом разрешить где указанно */
    const VISIBLE_DENY_ALLOW = -1;
    /** разрешить везде потом запретить где указано */
    const VISIBLE_ALLOW_DENY = 1;
    /** не учитывать настройки видимости */
    const VISIBLE_IGNORE = 0;

    /** правило обработки шаблона pathInfo содержит temlate */
    const RULE_CONTAIN = 'contain';
    /** правило обработки шаблона pathInfo начинается с temlate */
    const RULE_BEGIN = 'begins';
    /** правило обработки шаблона pathInfo заканчивается на template*/
    const RULE_END = 'ends';
    /** правило обработки шаблона pathInfo = temlate */
    const RULE_EQUAL = 'equal';
    /** правило обработки шаблона temlate в pathInfo с использованием PCRE */
    const RULE_PCRE = 'pcre';

    /** массив допустимых правил */
    const RULES_ARRAY = [
        self::RULE_CONTAIN,
        self::RULE_BEGIN,
        self::RULE_END,
        self::RULE_EQUAL,
        self::RULE_PCRE,
    ];

    /** массив допустимых типов видимости */
    const VISIBLE_ARRAY = [self::VISIBLE_ALLOW_DENY, self::VISIBLE_IGNORE, self::VISIBLE_DENY_ALLOW];
}