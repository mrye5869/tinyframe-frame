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

use og\console\Symfony\Component\Console\Formatter\OutputFormatter;
use og\console\Webmozart\Console\Adapter\StyleConverter;
use og\console\Webmozart\Console\Api\Formatter\Formatter;
use og\console\Webmozart\Console\Api\Formatter\Style;
use og\console\Webmozart\Console\Api\Formatter\StyleSet;

/**
 * A formatter that replaces style tags by ANSI format codes.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <55585190@qq.com>
 */
class AnsiFormatter implements Formatter
{
    /**
     * @var OutputFormatter
     */
    private $innerFormatter;

    /**
     * Creates the formatter.
     *
     * @param StyleSet $styleSet The style set to use.
     */
    public function __construct(StyleSet $styleSet = null)
    {
        $this->innerFormatter = new OutputFormatter(true);

        if (!$styleSet) {
            $styleSet = new DefaultStyleSet();
        }

        foreach ($styleSet->toArray() as $tag => $style) {
            $this->innerFormatter->setStyle($tag, StyleConverter::convert($style));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function format($string, Style $style = null)
    {
        if (null !== $style) {
            $this->innerFormatter->getStyleStack()->push(StyleConverter::convert($style));
        }

        $formatted = $this->innerFormatter->format($string);

        if (null !== $style) {
            $this->innerFormatter->getStyleStack()->pop();
        }

        return $formatted;
    }

    /**
     * {@inheritdoc}
     */
    public function removeFormat($string)
    {
        $this->innerFormatter->setDecorated(false);
        $formatted = $this->innerFormatter->format($string);
        $this->innerFormatter->setDecorated(true);

        return $formatted;
    }
}
