<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <55585190@qq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace og\console\Webmozart\Console\Formatter;

use og\console\Webmozart\Console\Api\Formatter\Style;
use og\console\Webmozart\Console\Api\Formatter\StyleSet;

/**
 * A color style which prefers cyan for its good readability.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <55585190@qq.com>
 */
class DefaultStyleSet extends StyleSet
{
    public function __construct()
    {
        $this->replace(array(
            // Default styles
            Style::tag('b')->bold(),
            Style::tag('u')->underlined(),
            Style::tag('bu')->bold()->underlined(),
            Style::tag('c1')->fgCyan(),
            Style::tag('c2')->fgYellow(),
            Style::tag('error')->fgWhite()->bgRed(),
            Style::tag('warn')->fgBlack()->bgYellow(),

            // Adapted Symfony default styles
            Style::tag('info')->fgCyan(),
            Style::tag('comment')->fgCyan(),
            Style::tag('question')->fgBlack()->bgCyan(),
        ));
    }
}
