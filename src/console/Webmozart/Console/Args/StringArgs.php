<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <55585190@qq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace og\console\Webmozart\Console\Args;

use og\console\Webmozart\Console\Api\Args\RawArgs;

/**
 * Console arguments passed as a string.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <55585190@qq.com>
 */
class StringArgs implements RawArgs
{
    /**
     * @var string[]
     */
    private $tokens;

    /**
     * Creates the console arguments.
     *
     * @param string $string The console arguments string.
     */
    public function __construct($string)
    {
        $parser = new TokenParser();

        $this->tokens = $parser->parseTokens($string);
    }

    /**
     * {@inheritdoc}
     */
    public function getScriptName()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function hasToken($token)
    {
        return in_array($token, $this->tokens);
    }

    /**
     * {@inheritdoc}
     */
    public function getTokens()
    {
        return $this->tokens;
    }

    /**
     * {@inheritdoc}
     */
    public function toString($scriptName = true)
    {
        return implode(' ', $this->tokens);
    }
}
