<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <55585190@qq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace og\console\Webmozart\Console\Api\Resolver;

use og\console\Webmozart\Console\Api\Args\Args;
use og\console\Webmozart\Console\Api\Args\RawArgs;
use og\console\Webmozart\Console\Api\Command\Command;

/**
 * A resolved command.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <55585190@qq.com>
 */
class ResolvedCommand
{
    /**
     * @var Command
     */
    private $command;

    /**
     * @var RawArgs
     */
    private $args;

    /**
     * Creates a new resolved command.
     *
     * @param Command $command The command.
     * @param Args    $args    The console arguments.
     */
    public function __construct(Command $command, Args $args)
    {
        $this->command = $command;
        $this->args = $args;
    }

    /**
     * Returns the command.
     *
     * @return Command The command.
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * Returns the parsed console arguments.
     *
     * @return Args The parsed console arguments.
     */
    public function getArgs()
    {
        return $this->args;
    }
}
