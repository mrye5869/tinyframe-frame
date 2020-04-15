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

use og\console\Symfony\Component\Console\Application;
use og\console\Symfony\Component\Console\Command\Command;
use og\console\Symfony\Component\Console\Helper\HelperSet;
use og\console\Symfony\Component\Console\Input\InputDefinition;
use og\console\Symfony\Component\Console\Input\InputInterface;
use og\console\Symfony\Component\Console\Output\OutputInterface;
use og\console\Webmozart\Assert\Assert;

/**
 * Adapts a `Command` instance of this package to Symfony's {@link Command} API.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <55585190@qq.com>
 */
abstract class AbstractCommandAdapter extends Command
{
    /**
     * @var \og\console\Webmozart\Console\Api\Command\Command
     */
    private $adaptedCommand;

    /**
     * Creates the adapter.
     *
     * @param \og\console\Webmozart\Console\Api\Command\Command $adaptedCommand The adapted command.
     * @param Application                            $application    The application.
     */
    public function __construct(\og\console\Webmozart\Console\Api\Command\Command $adaptedCommand, Application $application)
    {
        parent::setName($adaptedCommand->getName());

        parent::__construct();

        $this->adaptedCommand = $adaptedCommand;

        $config = $adaptedCommand->getConfig();

        parent::setDefinition(new ArgsFormatInputDefinition($this->adaptedCommand->getArgsFormat()));
        parent::setApplication($application);
        parent::setDescription($config->getDescription());
        parent::setHelp($config->getHelp());
        parent::setAliases($adaptedCommand->getAliases());

        if ($helperSet = $config->getHelperSet()) {
            parent::setHelperSet($helperSet);
        }
    }

    /**
     * Returns the adapted command.
     *
     * @return Command The adapted command.
     */
    public function getAdaptedCommand()
    {
        return $this->adaptedCommand;
    }

    /**
     * Does nothing.
     *
     * @param Application $application The application.
     *
     * @return static The current instance.
     */
    public function setApplication(Application $application = null)
    {
        return $this;
    }

    /**
     * Does nothing.
     *
     * @param HelperSet $helperSet The helper set.
     *
     * @return static The current instance.
     */
    public function setHelperSet(HelperSet $helperSet)
    {
        return $this;
    }

    /**
     * Does nothing.
     *
     * @param array|InputDefinition $definition The definition
     *
     * @return static The current instance.
     */
    public function setDefinition($definition)
    {
        return $this;
    }

    /**
     * Does nothing.
     *
     * @param string $name The name.
     *
     * @return static The current instance.
     */
    public function setName($name)
    {
        return $this;
    }

    /**
     * Does nothing.
     *
     * @param string $title The process title.
     *
     * @return static The current instance.
     */
    public function setProcessTitle($title)
    {
        return $this;
    }

    /**
     * Does nothing.
     *
     * @param string $description The description.
     *
     * @return static The current instance.
     */
    public function setDescription($description)
    {
        return $this;
    }

    /**
     * Does nothing.
     *
     * @param string $help The help.
     *
     * @return static The current instance.
     */
    public function setHelp($help)
    {
        return $this;
    }

    /**
     * Does nothing.
     *
     * @param string[] $aliases The aliases.
     *
     * @return static The current instance.
     */
    public function setAliases($aliases)
    {
        return $this;
    }

    /**
     * Does nothing.
     *
     * @param bool $mergeArgs
     *
     * @return static The current instance.
     */
    public function mergeApplicationDefinition($mergeArgs = true)
    {
        return $this;
    }

    /**
     * Does nothing.
     *
     * @param string $name
     * @param null   $mode
     * @param string $description
     * @param null   $default
     *
     * @return static The current instance.
     */
    public function addArgument($name, $mode = null, $description = '', $default = null)
    {
        return $this;
    }

    /**
     * Does nothing.
     *
     * @param string $name
     * @param null   $shortcut
     * @param null   $mode
     * @param string $description
     * @param null   $default
     *
     * @return static The current instance.
     */
    public function addOption($name, $shortcut = null, $mode = null, $description = '', $default = null)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return $this->adaptedCommand->getConfig()->isEnabled();
    }

    /**
     * Executes the command.
     *
     * @param InputInterface  $input  The console input.
     * @param OutputInterface $output The console output.
     *
     * @return int The exit status.
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        /* @var ArgsInput $input */
        /* @var IOOutput $output */
        Assert::isInstanceOf($input, 'og\console\Webmozart\Console\Adapter\ArgsInput');
        Assert::isInstanceOf($output, 'og\console\Webmozart\Console\Adapter\IOOutput');

        return $this->adaptedCommand->handle($input->getArgs(), $output->getIO());
    }
}

if (method_exists('og\console\Symfony\Component\Console\Command\Command', 'asText')) {
    // Symfony 2.0 compatible definition
    class CommandAdapter extends AbstractCommandAdapter
    {
        /**
         * Does nothing.
         *
         * @param callable $code The code.
         *
         * @return static The current instance.
         */
        public function setCode($code)
        {
            return $this;
        }
    }
} else {
    // Symfony 3.0 compatible definition
    class CommandAdapter extends AbstractCommandAdapter
    {
        /**
         * Does nothing.
         *
         * @param callable $code The code.
         *
         * @return static The current instance.
         */
        public function setCode(callable $code)
        {
            return $this;
        }
    }
}
