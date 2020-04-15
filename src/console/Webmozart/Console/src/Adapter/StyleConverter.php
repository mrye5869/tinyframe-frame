<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <55585190@qq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace og\console\Webmozart\Console\Adapter;

use og\console\Symfony\Component\Console\Formatter\OutputFormatterStyle;
use og\console\Webmozart\Console\Api\Formatter\Style;

/**
 * Converts {@link Style} instances to Symfony's {@link OutputFormatterStyle}.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <55585190@qq.com>
 */
class StyleConverter
{
    /**
     * Converts a {@link Style} instance to an {@link OutputFormatterStyle}.
     *
     * @param Style $style The style to convert.
     *
     * @return OutputFormatterStyle The converted style.
     */
    public static function convert(Style $style)
    {
        $options = array();

        if ($style->isBold()) {
            $options[] = 'bold';
        }

        if ($style->isBlinking()) {
            $options[] = 'blink';
        }

        if ($style->isUnderlined()) {
            $options[] = 'underscore';
        }

        if ($style->isInverse()) {
            $options[] = 'reverse';
        }

        if ($style->isHidden()) {
            $options[] = 'conceal';
        }

        return new OutputFormatterStyle($style->getForegroundColor(), $style->getBackgroundColor(), $options);
    }

    private function __construct()
    {
    }
}
