<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <55585190@qq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace og\console\Symfony\Component\Console\Event;

use og\console\Symfony\Component\Console\Command\Command;
use og\console\Symfony\Component\Console\Input\InputInterface;
use og\console\Symfony\Component\Console\Output\OutputInterface;

/**
 * Allows to manipulate the exit code of a command after its execution.
 *
 * @author Francesco Levorato <git@flevour.net>
 */
class ConsoleTerminateEvent extends ConsoleEvent
{
    /**
     * The exit code of the command.
     *
     * @var int
     */
    private $exitCode;

    public function __construct(Command $command, InputInterface $input, OutputInterface $output, $exitCode)
    {
        parent::__construct($command, $input, $output);

        $this->setExitCode($exitCode);
    }

    /**
     * Sets the exit code.
     *
     * @param int $exitCode The command exit code
     */
    public function setExitCode($exitCode)
    {
        $this->exitCode = (int) $exitCode;
    }

    /**
     * Gets the exit code.
     *
     * @return int The command exit code
     */
    public function getExitCode()
    {
        return $this->exitCode;
    }
}
