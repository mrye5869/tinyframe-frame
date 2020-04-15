<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <55585190@qq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace og\console\Webmozart\Console\Handler\Help;

use og\console\Webmozart\Console\Api\Args\Args;
use og\console\Webmozart\Console\Api\Command\Command;
use og\console\Webmozart\Console\Api\IO\IO;
use og\console\Webmozart\Console\UI\Help\ApplicationHelp;
use og\console\Webmozart\Console\UI\Help\CommandHelp;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <55585190@qq.com>
 */
class HelpTextHandler
{
    /**
     * {@inheritdoc}
     */
    public function handle(Args $args, IO $io, Command $command)
    {
        $application = $command->getApplication();

        if ($args->isArgumentSet('command')) {
            $theCommand = $application->getCommand($args->getArgument('command'));
            $usage = new CommandHelp($theCommand);
        } else {
            $usage = new ApplicationHelp($application);
        }

        $usage->render($io);

        return 0;
    }
}
