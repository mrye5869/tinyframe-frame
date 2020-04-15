<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <55585190@qq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace og\console\Webmozart\Console\Api\Config;

use og\console\Symfony\Component\EventDispatcher\EventDispatcher;
use og\console\Symfony\Component\EventDispatcher\EventDispatcherInterface;
use og\console\Symfony\Component\EventDispatcher\EventSubscriberInterface;
use og\console\Webmozart\Assert\Assert;
use og\console\Webmozart\Console\Api\Command\NoSuchCommandException;
use og\console\Webmozart\Console\Api\Formatter\Style;
use og\console\Webmozart\Console\Api\Formatter\StyleSet;
use og\console\Webmozart\Console\Api\Resolver\CommandResolver;
use og\console\Webmozart\Console\Formatter\DefaultStyleSet;
use og\console\Webmozart\Console\Resolver\DefaultResolver;

/**
 * The configuration of a console application.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <55585190@qq.com>
 */
class ApplicationConfig extends Config
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $displayName;

    /**
     * @var string
     */
    private $version;

    /**
     * @var string
     */
    private $help;

    /**
     * @var CommandConfig[]
     */
    private $commandConfigs = array();

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var bool
     */
    private $catchExceptions = true;

    /**
     * @var bool
     */
    private $terminateAfterRun = true;

    /**
     * @var CommandResolver
     */
    private $commandResolver;

    /**
     * @var callable
     */
    private $ioFactory;

    /**
     * @var bool
     */
    private $debug = false;

    /**
     * @var StyleSet
     */
    private $styleSet;

    /**
     * Creates a new console application.
     *
     * @param string $name    The name of the application.
     * @param string $version The application version.
     *
     * @return static The created instance.
     */
    public static function create($name = null, $version = null)
    {
        return new static($name, $version);
    }

    /**
     * Creates a new console application.
     *
     * @param string $name    The name of the application.
     * @param string $version The application version.
     */
    public function __construct($name = null, $version = null)
    {
        $this->name = $name;
        $this->version = $version;

        parent::__construct();
    }

    /**
     * Returns the name of the application.
     *
     * @return string The application name.
     *
     * @see setName()
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the name of the application.
     *
     * @param string $name The application name.
     *
     * @return static The current instance.
     *
     * @see getName()
     */
    public function setName($name)
    {
        if (null !== $name) {
            Assert::string($name, 'The application name must be a string. Got: %s');
            Assert::notEmpty($name, 'The application name must not be empty.');
            Assert::regex($name, '~^[a-zA-Z0-9\-]+$~', 'The application name must contain letters, numbers and hyphens only. Did you mean to call setDisplayName()?');
        }

        $this->name = $name;

        return $this;
    }

    /**
     * Returns the application name as it is displayed in the help.
     *
     * If no display name is set with {@link setDisplayName()}, the humanized
     * application name is returned.
     *
     * @return string The display name.
     *
     * @see setDisplayName()
     */
    public function getDisplayName()
    {
        return $this->displayName ?: $this->getDefaultDisplayName();
    }

    /**
     * Sets the application name as it is displayed in the help.
     *
     * @param string $displayName The display name.
     *
     * @return static The current instance.
     *
     * @see getDisplayName()
     */
    public function setDisplayName($displayName)
    {
        if (null !== $displayName) {
            Assert::string($displayName, 'The display name must be a string. Got: %s');
            Assert::notEmpty($displayName, 'The display name must not be empty.');
        }

        $this->displayName = $displayName;

        return $this;
    }

    /**
     * Returns the version of the application.
     *
     * @return string The application version.
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Sets the version of the application.
     *
     * @param string $version The application version.
     *
     * @return static The current instance.
     */
    public function setVersion($version)
    {
        if (null !== $version) {
            Assert::string($version, 'The application version must be a string. Got: %s');
            Assert::notEmpty($version, 'The application version must not be empty.');
        }

        $this->version = $version;

        return $this;
    }

    /**
     * Returns the help text of the application.
     *
     * @return string The help text.
     */
    public function getHelp()
    {
        return $this->help;
    }

    /**
     * Sets the help text of the application.
     *
     * @param string $help The help text.
     *
     * @return static The current instance.
     */
    public function setHelp($help)
    {
        if (null !== $help) {
            Assert::string($help, 'The help text must be a string. Got: %s');
            Assert::notEmpty($help, 'The help text must not be empty.');
        }

        $this->help = $help;

        return $this;
    }

    /**
     * Returns the event dispatcher used to dispatch the console events.
     *
     * @return EventDispatcherInterface The event dispatcher.
     */
    public function getEventDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * Sets the event dispatcher for dispatching the console events.
     *
     * @param EventDispatcherInterface $dispatcher The event dispatcher.
     *
     * @return static The current instance.
     */
    public function setEventDispatcher(EventDispatcherInterface $dispatcher = null)
    {
        $this->dispatcher = $dispatcher;

        return $this;
    }

    /**
     * Adds a listener for the given event name.
     *
     * See {@link ConsoleEvents} for the supported event names.
     *
     * @param string   $eventName The event to listen to.
     * @param callable $listener  The callback to execute when the event is
     *                            dispatched.
     * @param int      $priority  The event priority.
     *
     * @return static The current instance.
     *
     * @see EventDispatcherInterface::addListener()
     */
    public function addEventListener($eventName, $listener, $priority = 0)
    {
        if (!$this->dispatcher) {
            $this->dispatcher = new EventDispatcher();
        }

        $this->dispatcher->addListener($eventName, $listener, $priority);

        return $this;
    }

    /**
     * Adds an event subscriber to the dispatcher.
     *
     * @param EventSubscriberInterface $subscriber The subscriber to add.
     *
     * @return static The current instance.
     *
     * @see EventDispatcherInterface::addSubscriber()
     */
    public function addEventSubscriber(EventSubscriberInterface $subscriber)
    {
        if (!$this->dispatcher) {
            $this->dispatcher = new EventDispatcher();
        }

        $this->dispatcher->addSubscriber($subscriber);

        return $this;
    }

    /**
     * Removes an event listener for the given event name.
     *
     * @param string   $eventName The event name.
     * @param callable $listener  The callback to remove.
     *
     * @return static The current instance.
     *
     * @see EventDispatcherInterface::removeListener()
     */
    public function removeEventListener($eventName, $listener)
    {
        if (!$this->dispatcher) {
            $this->dispatcher = new EventDispatcher();
        }

        $this->dispatcher->removeListener($eventName, $listener);

        return $this;
    }

    /**
     * Removes an event subscriber from the dispatcher.
     *
     * @param EventSubscriberInterface $subscriber The subscriber to remove.
     *
     * @return static The current instance.
     *
     * @see EventDispatcherInterface::removeSubscriber()
     */
    public function removeEventSubscriber(EventSubscriberInterface $subscriber)
    {
        if (!$this->dispatcher) {
            $this->dispatcher = new EventDispatcher();
        }

        $this->dispatcher->removeSubscriber($subscriber);

        return $this;
    }

    /**
     * Returns whether the application catches and displays exceptions thrown
     * while running a command.
     *
     * @return bool Returns `true` if exceptions are caught and `false`
     *              otherwise.
     *
     * @see setCatchExceptions()
     */
    public function isExceptionCaught()
    {
        return $this->catchExceptions;
    }

    /**
     * Sets whether the application catches and displays exceptions thrown
     * while running a command.
     *
     * @param bool $catch Whether to catch and display exceptions thrown
     *                    while running a command.
     *
     * @return static The current instance.
     *
     * @see isExceptionCaught()
     */
    public function setCatchExceptions($catch)
    {
        Assert::boolean($catch);

        $this->catchExceptions = $catch;

        return $this;
    }

    /**
     * Returns whether the PHP process is terminated after running a command.
     *
     * @return bool Returns `true` if the PHP process is terminated after
     *              {@link run()} and `false` otherwise.
     *
     * @see setTerminateAfterRun()
     */
    public function isTerminatedAfterRun()
    {
        return $this->terminateAfterRun;
    }

    /**
     * Sets whether to terminate the PHP process after running a command.
     *
     * @param bool $terminate Whether to terminate the PHP process after
     *                        running a command.
     *
     * @return static The current instance.
     *
     * @see isTerminatedAfterRun()
     */
    public function setTerminateAfterRun($terminate)
    {
        Assert::boolean($terminate);

        $this->terminateAfterRun = $terminate;

        return $this;
    }

    /**
     * Returns the used command resolver.
     *
     * @return CommandResolver The command resolver.
     *
     * @see setCommandResolver()
     */
    public function getCommandResolver()
    {
        if (!$this->commandResolver) {
            $this->commandResolver = new DefaultResolver();
        }

        return $this->commandResolver;
    }

    /**
     * Sets the used command resolver.
     *
     * @param CommandResolver $commandResolver The command resolver.
     *
     * @return static The current instance.
     *
     * @see getCommandResolver()
     */
    public function setCommandResolver(CommandResolver $commandResolver)
    {
        $this->commandResolver = $commandResolver;

        return $this;
    }

    /**
     * Returns the callable used to create {@link IO} instances.
     *
     * @return callable The callable or `null` if none was set.
     *
     * @see setIOFactory()
     */
    public function getIOFactory()
    {
        return $this->ioFactory;
    }

    /**
     * Sets the callable used to create {@link IO} instances.
     *
     * The callable receives four arguments:
     *
     *  * {@link RawArgs}: The raw console arguments.
     *  * {@link Input}: The input.
     *  * {@link Output}: The output.
     *  * {@link Output}: The error output.
     *
     * The input and output instances may be `null` if none were passed to
     * {@link Application::run()}.
     *
     * @param callable $ioFactory The {@link IO} factory callable.
     *
     * @return static The current instance.
     */
    public function setIOFactory($ioFactory)
    {
        Assert::nullOrIsCallable($ioFactory, 'The IO factory must be a callable or null. Got: %s');

        $this->ioFactory = $ioFactory;

        return $this;
    }

    /**
     * Returns whether the application is in debug mode.
     *
     * In debug mode, the verbosity is always {@link IO::DEBUG}.
     *
     * @return bool Returns `true` if the application is in debug mode.
     *
     * @see setDebug()
     */
    public function isDebug()
    {
        return $this->debug;
    }

    /**
     * Sets whether the application is in debug mode.
     *
     * In debug mode, the verbosity is always {@link IO::DEBUG}.
     *
     * @param bool $debug Set to `true` to activate the debug mode.
     *
     * @return static The current instance.
     *
     * @see isDebug()
     */
    public function setDebug($debug)
    {
        Assert::boolean($debug);

        $this->debug = $debug;

        return $this;
    }

    /**
     * Returns the configured style set.
     *
     * @return StyleSet The style set.
     *
     * @see setStyleSet()
     */
    public function getStyleSet()
    {
        if (!$this->styleSet) {
            $this->styleSet = new DefaultStyleSet();
        }

        return $this->styleSet;
    }

    /**
     * Sets the used style set.
     *
     * @param StyleSet $styleSet The style set to use.
     *
     * @return static The current instance.
     *
     * @see getStyleSet()
     */
    public function setStyleSet(StyleSet $styleSet)
    {
        $this->styleSet = $styleSet;

        return $this;
    }

    /**
     * Adds a style to the style set.
     *
     * @param Style $style The style to add.
     *
     * @return static The current instance.
     *
     * @see StyleSet::add()
     */
    public function addStyle(Style $style)
    {
        if (!$this->styleSet) {
            $this->styleSet = new DefaultStyleSet();
        }

        $this->styleSet->add($style);

        return $this;
    }

    /**
     * Adds multiple styles to the style set.
     *
     * @param Style[] $styles The styles to add.
     *
     * @return static The current instance.
     *
     * @see StyleSet::merge()
     */
    public function addStyles(array $styles)
    {
        if (!$this->styleSet) {
            $this->styleSet = new DefaultStyleSet();
        }

        $this->styleSet->merge($styles);

        return $this;
    }

    /**
     * Removes a style from the style set.
     *
     * @param string $tag The tag of the style to remove.
     *
     * @return static The current instance.
     *
     * @see StyleSet::remove()
     */
    public function removeStyle($tag)
    {
        if ($this->styleSet) {
            $this->styleSet->remove($tag);
        }

        return $this;
    }

    /**
     * Starts a configuration block for a command.
     *
     * The configuration of the command is returned by this method. You can use
     * the fluent interface to configure the sub-command before jumping back to
     * this configuration with {@link CommandConfig::end()}:
     *
     * ```php
     * protected function configure()
     * {
     *     $this
     *         ->setName('server')
     *         ->setDescription('List and manage servers')
     *
     *         ->beginCommand('add')
     *             ->setDescription('Add a server')
     *             ->addArgument('host', Argument::REQUIRED)
     *             ->addOption('port', 'p', Option::VALUE_OPTIONAL, null, 80)
     *         ->end()
     *
     *         // ...
     *     ;
     * }
     * ```
     *
     * @param string $name The name of the command.
     *
     * @return CommandConfig The command configuration.
     *
     * @see editCommand()
     */
    public function beginCommand($name)
    {
        $commandConfig = new CommandConfig($name, $this);

        // The name is dynamic, so don't store by name
        $this->commandConfigs[] = $commandConfig;

        return $commandConfig;
    }

    /**
     * Alias for {@link getCommandConfig()}.
     *
     * This method can be used to nicely edit a command inherited from a
     * parent configuration using the fluent API:
     *
     * ```php
     * protected function configure()
     * {
     *     parent::configure();
     *
     *     $this
     *         ->editCommand('add')
     *             // ...
     *         ->end()
     *
     *         // ...
     *     ;
     * }
     * ```
     *
     * @param string $name The name of the command to edit.
     *
     * @return CommandConfig The command configuration.
     *
     * @see beginCommand()
     */
    public function editCommand($name)
    {
        return $this->getCommandConfig($name);
    }

    /**
     * Adds a command configuration to the application.
     *
     * @param CommandConfig $config The command configuration.
     *
     * @return static The current instance.
     *
     * @see beginCommand()
     */
    public function addCommandConfig(CommandConfig $config)
    {
        // The name is dynamic, so don't store by name
        $this->commandConfigs[] = $config;

        return $this;
    }

    /**
     * Adds command configurations to the application.
     *
     * @param CommandConfig[] $configs The command configurations.
     *
     * @return static The current instance.
     *
     * @see beginCommand()
     */
    public function addCommandConfigs(array $configs)
    {
        foreach ($configs as $command) {
            $this->addCommandConfig($command);
        }

        return $this;
    }

    /**
     * Sets the command configurations of the application.
     *
     * @param CommandConfig[] $configs The command configurations.
     *
     * @return static The current instance.
     *
     * @see beginCommand()
     */
    public function setCommandConfigs(array $configs)
    {
        $this->commandConfigs = array();

        $this->addCommandConfigs($configs);

        return $this;
    }

    /**
     * Returns the command configuration for a given name.
     *
     * @param string $name The name of the command.
     *
     * @return CommandConfig The command configuration.
     *
     * @throws NoSuchCommandException If the command configuration is not found.
     *
     * @see beginCommand()
     */
    public function getCommandConfig($name)
    {
        foreach ($this->commandConfigs as $commandConfig) {
            if ($name === $commandConfig->getName()) {
                return $commandConfig;
            }
        }

        throw NoSuchCommandException::forCommandName($name);
    }

    /**
     * Returns all registered command configurations.
     *
     * @return CommandConfig[] The command configurations.
     *
     * @see beginCommand()
     */
    public function getCommandConfigs()
    {
        return $this->commandConfigs;
    }

    /**
     * Returns whether the application has a command with a given name.
     *
     * @param string $name The name of the command.
     *
     * @return bool Returns `true` if the command configuration with the given
     *              name exists and `false` otherwise.
     *
     * @see beginCommand()
     */
    public function hasCommandConfig($name)
    {
        foreach ($this->commandConfigs as $commandConfig) {
            if ($name === $commandConfig->getName()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns whether the application has any registered command configurations.
     *
     * @return bool Returns `true` if command configurations were added to the
     *              application and `false` otherwise.
     *
     * @see beginCommand()
     */
    public function hasCommandConfigs()
    {
        return count($this->commandConfigs) > 0;
    }

    /**
     * Returns the default display name used if no display name is set.
     *
     * @return string The default display name.
     */
    protected function getDefaultDisplayName()
    {
        if (!$this->name) {
            return null;
        }

        return ucwords(preg_replace('~[\s-_]+~', ' ', $this->name));
    }
}
