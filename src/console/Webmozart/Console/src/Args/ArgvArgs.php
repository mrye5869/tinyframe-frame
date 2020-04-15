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
 * Console arguments passed via PHP's "argv" variable.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <55585190@qq.com>
 */
class ArgvArgs implements RawArgs
{
    /**
     * @var string
     */
    private $scriptName;

    /**
     * @var string[]
     */
    private $tokens;

    /**
     * Creates the console arguments.
     *
     * @param array $argv The contents of the "argv" variable or `null` to read
     *                    the global "argv" variable.
     */
    public function __construct(array $argv = null)
    {
        if (null === $argv) {
            $argv = $_SERVER['argv'];
        }

        $this->scriptName = array_shift($argv);
        $this->tokens = $argv;
    }

    /**
     * {@inheritdoc}
     */
    public function getScriptName()
    {
        return $this->scriptName;
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
        $string = implode(' ', $this->tokens);

        if ($scriptName) {
            $string = ltrim($this->scriptName.' ').$string;
        }

        return $string;
    }
}
