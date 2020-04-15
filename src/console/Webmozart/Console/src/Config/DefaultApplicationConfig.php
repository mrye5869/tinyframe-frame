<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <55585190@qq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace og\console\Webmozart\Console\Config;

use og\console\Webmozart\Console\Api\Application\Application;
use og\console\Webmozart\Console\Api\Args\Format\Argument;
use og\console\Webmozart\Console\Api\Args\Format\Option;
use og\console\Webmozart\Console\Api\Args\RawArgs;
use og\console\Webmozart\Console\Api\Config\ApplicationConfig;
use og\console\Webmozart\Console\Api\Event\ConsoleEvents;
use og\console\Webmozart\Console\Api\Event\PreHandleEvent;
use og\console\Webmozart\Console\Api\Event\PreResolveEvent;
use og\console\Webmozart\Console\Api\IO\Input;
use og\console\Webmozart\Console\Api\IO\InputStream;
use og\console\Webmozart\Console\Api\IO\IO;
use og\console\Webmozart\Console\Api\IO\Output;
use og\console\Webmozart\Console\Api\IO\OutputStream;
use og\console\Webmozart\Console\Api\Resolver\ResolvedCommand;
use og\console\Webmozart\Console\Formatter\AnsiFormatter;
use og\console\Webmozart\Console\Formatter\PlainFormatter;
use og\console\Webmozart\Console\Handler\Help\HelpHandler;
use og\console\Webmozart\Console\IO\ConsoleIO;
use og\console\Webmozart\Console\IO\InputStream\StandardInputStream;
use og\console\Webmozart\Console\IO\OutputStream\ErrorOutputStream;
use og\console\Webmozart\Console\IO\OutputStream\StandardOutputStream;
use og\console\Webmozart\Console\UI\Component\NameVersion;

/**
 * The default application configuration.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <55585190@qq.com>
 */
class DefaultApplicationConfig extends ApplicationConfig
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setIOFactory(array($this, 'createIO'))
            ->addEventListener(ConsoleEvents::PRE_RESOLVE, array($this, 'resolveHelpCommand'))
            ->addEventListener(ConsoleEvents::PRE_HANDLE, array($this, 'printVersion'))

            ->addOption('help', 'h', Option::NO_VALUE, 'Display help about the command')
            ->addOption('quiet', 'q', Option::NO_VALUE, 'Do not output any message')
            ->addOption('verbose', 'v', Option::OPTIONAL_VALUE, 'Increase the verbosity of messages: "-v" for normal output, "-vv" for more verbose output and "-vvv" for debug', null, 'level')
            ->addOption('version', 'V', Option::NO_VALUE, 'Display this application version')
            ->addOption('ansi', null, Option::NO_VALUE, 'Force ANSI output')
            ->addOption('no-ansi', null, Option::NO_VALUE, 'Disable ANSI output')
            ->addOption('no-interaction', 'n', Option::NO_VALUE, 'Do not ask any interactive question')

            ->beginCommand('help')
                ->markDefault()
                ->setDescription('Display the manual of a command')
                ->addArgument('command', Argument::OPTIONAL, 'The command name')
                ->addOption('man', 'm', Option::NO_VALUE, 'Output the help as man page')
                ->addOption('ascii-doc', null, Option::NO_VALUE, 'Output the help as AsciiDoc document')
                ->addOption('text', 't', Option::NO_VALUE, 'Output the help as plain text')
                ->addOption('xml', 'x', Option::NO_VALUE, 'Output the help as XML')
                ->addOption('json', 'j', Option::NO_VALUE, 'Output the help as JSON')
                ->setHandler(function () { return new HelpHandler(); })
            ->end()
        ;
    }

    public function createIO(Application $application, RawArgs $args, InputStream $inputStream = null, OutputStream $outputStream = null, OutputStream $errorStream = null)
    {
        $inputStream = $inputStream ?: new StandardInputStream();
        $outputStream = $outputStream ?: new StandardOutputStream();
        $errorStream = $errorStream ?: new ErrorOutputStream();
        $styleSet = $application->getConfig()->getStyleSet();

        if ($args->hasToken('--no-ansi')) {
            $outputFormatter = $errorFormatter = new PlainFormatter($styleSet);
        } elseif ($args->hasToken('--ansi')) {
            $outputFormatter = $errorFormatter = new AnsiFormatter($styleSet);
        } else {
            $outputFormatter = $outputStream->supportsAnsi() ? new AnsiFormatter($styleSet) : new PlainFormatter($styleSet);
            $errorFormatter = $errorStream->supportsAnsi() ? new AnsiFormatter($styleSet) : new PlainFormatter($styleSet);
        }

        $io = new ConsoleIO(
            new Input($inputStream),
            new Output($outputStream, $outputFormatter),
            new Output($errorStream, $errorFormatter)
        );

        if ($args->hasToken('-vvv') || $this->isDebug()) {
            $io->setVerbosity(IO::DEBUG);
        } elseif ($args->hasToken('-vv')) {
            $io->setVerbosity(IO::VERY_VERBOSE);
        } elseif ($args->hasToken('-v')) {
            $io->setVerbosity(IO::VERBOSE);
        }

        if ($args->hasToken('--quiet') || $args->hasToken('-q')) {
            $io->setQuiet(true);
        }

        if ($args->hasToken('--no-interaction') || $args->hasToken('-n')) {
            $io->setInteractive(false);
        }

        return $io;
    }

    public function resolveHelpCommand(PreResolveEvent $event)
    {
        $args = $event->getRawArgs();

        if ($args->hasToken('-h') || $args->hasToken('--help')) {
            $command = $event->getApplication()->getCommand('help');

            // Enable lenient args parsing
            $parsedArgs = $command->parseArgs($args, true);

            $event->setResolvedCommand(new ResolvedCommand($command, $parsedArgs));
            $event->stopPropagation();
        }
    }

    public function printVersion(PreHandleEvent $event)
    {
        if ($event->getArgs()->isOptionSet('version')) {
            $version = new NameVersion($event->getCommand()->getApplication()->getConfig());
            $version->render($event->getIO());

            $event->setHandled(true);
        }
    }
}
