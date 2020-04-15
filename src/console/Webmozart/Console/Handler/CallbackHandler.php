<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <55585190@qq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace og\console\Webmozart\Console\Handler;

use og\console\Webmozart\Assert\Assert;
use og\console\Webmozart\Console\Api\Args\Args;
use og\console\Webmozart\Console\Api\Command\Command;
use og\console\Webmozart\Console\Api\IO\IO;

/**
 * Delegates command handling to a callable.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <55585190@qq.com>
 */
class CallbackHandler
{
    /**
     * @var callable
     */
    private $callback;

    /**
     * Creates the command handler.
     *
     * The passed callback receives three arguments:
     *
     *  * {@link Args} `$args`: The console arguments.
     *  * {@link IO} `$io`: The I/O.
     *  * {@link Command} `$command`: The executed command.
     *
     * The callable should return 0 on success and a positive integer on error.
     *
     * @param callable $callback The callback to execute when handling a
     *                           command.
     */
    public function __construct($callback)
    {
        Assert::isCallable($callback);

        $this->callback = $callback;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Args $args, IO $io, Command $command)
    {
        return call_user_func($this->callback, $args, $io, $command);
    }
}
