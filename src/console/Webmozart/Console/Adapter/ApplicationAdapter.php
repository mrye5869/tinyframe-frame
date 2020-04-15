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

use Exception;
use og\console\Symfony\Component\Console\Application;
use og\console\Symfony\Component\Console\Input\InputInterface;
use og\console\Symfony\Component\Console\Output\OutputInterface;
use og\console\Webmozart\Assert\Assert;

/**
 * Adapts an `Application` instance of this package to Symfony's
 * {@link Application} API.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <55585190@qq.com>
 */
class ApplicationAdapter extends Application
{
    /**
     * @var \og\console\Webmozart\Console\Api\Application\Application
     */
    private $adaptedApplication;

    /**
     * @var CommandAdapter
     */
    private $currentCommand;

    /**
     * Creates the application.
     *
     * @param \og\console\Webmozart\Console\Api\Application\Application $application
     */
    public function __construct(\og\console\Webmozart\Console\Api\Application\Application $application)
    {
        $this->adaptedApplication = $application;

        $config = $application->getConfig();

        parent::__construct($config->getDisplayName(), $config->getVersion());

        if ($dispatcher = $config->getEventDispatcher()) {
            $this->setDispatcher($dispatcher);
        }

        $this->setAutoExit($config->isTerminatedAfterRun());
        $this->setCatchExceptions($config->isExceptionCaught());

        foreach ($application->getCommands() as $command) {
            $this->add(new CommandAdapter($command, $this));
        }
    }

    /**
     * @return \og\console\Webmozart\Console\Api\Application\Application
     */
    public function getAdaptedApplication()
    {
        return $this->adaptedApplication;
    }

    /**
     * {@inheritdoc}
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        /* @var ArgsInput $input */
        Assert::isInstanceOf($input, 'og\console\Webmozart\Console\Adapter\ArgsInput');

        $rawArgs = $input->getRawArgs();
        $resolvedCommand = $this->adaptedApplication->resolveCommand($rawArgs);

        // Add parsed Args to the adapter
        $input = new ArgsInput($rawArgs, $resolvedCommand->getArgs());

        // Don't use $this->get() as get() does not work for sub-commands
        $this->currentCommand = new CommandAdapter($resolvedCommand->getCommand(), $this);

        try {
            $result = parent::doRun($input, $output);
            $this->currentCommand = null;
        } catch (Exception $e) {
            $this->currentCommand = null;

            throw $e;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function getCommandName(InputInterface $input)
    {
        // This method must return something, otherwise the base class tries
        // to set the "command" argument which doesn't usually exist
        return 'command-name';
    }

    /**
     * {@inheritdoc}
     */
    public function find($name)
    {
        return $this->currentCommand;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultInputDefinition()
    {
        return new ArgsFormatInputDefinition($this->adaptedApplication->getGlobalArgsFormat());
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultCommands()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultHelperSet()
    {
        return $this->adaptedApplication->getConfig()->getHelperSet();
    }
}
