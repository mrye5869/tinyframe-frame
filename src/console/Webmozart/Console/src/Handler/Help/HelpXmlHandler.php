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

use og\console\Symfony\Component\Console\Descriptor\XmlDescriptor;
use og\console\Webmozart\Console\Adapter\ApplicationAdapter;
use og\console\Webmozart\Console\Adapter\CommandAdapter;
use og\console\Webmozart\Console\Adapter\IOOutput;
use og\console\Webmozart\Console\Api\Args\Args;
use og\console\Webmozart\Console\Api\Command\Command;
use og\console\Webmozart\Console\Api\IO\IO;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <55585190@qq.com>
 */
class HelpXmlHandler
{
    /**
     * {@inheritdoc}
     */
    public function handle(Args $args, IO $io, Command $command)
    {
        $descriptor = new XmlDescriptor();
        $output = new IOOutput($io);
        $application = $command->getApplication();
        $applicationAdapter = new ApplicationAdapter($application);

        if ($args->isArgumentSet('command')) {
            $theCommand = $application->getCommand($args->getArgument('command'));
            $commandAdapter = new CommandAdapter($theCommand, $applicationAdapter);
            $descriptor->describe($output, $commandAdapter);
        } else {
            $descriptor->describe($output, $applicationAdapter);
        }

        return 0;
    }
}
