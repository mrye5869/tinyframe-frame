<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <55585190@qq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace og\console\Webmozart\Console\UI\Help;

use og\console\Webmozart\Console\Api\Args\Format\Argument;
use og\console\Webmozart\Console\Api\Args\Format\Option;
use og\console\Webmozart\Console\Api\Command\Command;
use og\console\Webmozart\Console\Api\Command\CommandCollection;
use og\console\Webmozart\Console\Api\Config\OptionCommandConfig;
use og\console\Webmozart\Console\UI\Component\EmptyLine;
use og\console\Webmozart\Console\UI\Component\Paragraph;
use og\console\Webmozart\Console\UI\Layout\BlockLayout;

/**
 * Renders the help of a console command.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <55585190@qq.com>
 */
class CommandHelp extends AbstractHelp
{
    /**
     * @var Command
     */
    private $command;

    /**
     * Creates the help.
     *
     * @param Command $command The command to render.
     */
    public function __construct(Command $command)
    {
        $this->command = $command;
    }

    /**
     * {@inheritdoc}
     */
    protected function renderHelp(BlockLayout $layout)
    {
        $help = $this->command->getConfig()->getHelp();
        $argsFormat = $this->command->getArgsFormat();
        $subCommands = $this->command->getNamedSubCommands();

        $this->renderUsage($layout, $this->command);

        if ($argsFormat->hasArguments()) {
            $this->renderArguments($layout, $argsFormat->getArguments());
        }

        if (!$subCommands->isEmpty()) {
            $this->renderSubCommands($layout, $subCommands);
        }

        if ($argsFormat->hasOptions(false)) {
            $this->renderOptions($layout, $argsFormat->getOptions(false));
        }

        if ($argsFormat->getBaseFormat() && $argsFormat->getBaseFormat()->hasOptions()) {
            $this->renderGlobalOptions($layout, $argsFormat->getBaseFormat()->getOptions());
        }

        if ($help) {
            $this->renderDescription($layout, $help);
        }
    }

    /**
     * Renders the "Usage" section.
     *
     * @param BlockLayout $layout  The layout.
     * @param Command     $command The command to render.
     */
    protected function renderUsage(BlockLayout $layout, Command $command)
    {
        $formatsToPrint = array();

        // Start with the default commands
        if ($command->hasDefaultSubCommands()) {
            // If the command has default commands, print them
            foreach ($command->getDefaultSubCommands() as $subCommand) {
                // The name of the sub command is only optional (i.e. printed
                // wrapped in brackets: "[sub]") if the command is not
                // anonymous
                $nameOptional = !$subCommand->getConfig()->isAnonymous();

                $formatsToPrint[] = array($subCommand->getArgsFormat(), $nameOptional);
            }
        } else {
            // Otherwise print the command's usage itself
            $formatsToPrint[] = array($command->getArgsFormat(), false);
        }

        // Add remaining sub-commands
        foreach ($command->getSubCommands() as $subCommand) {
            // Don't duplicate default commands
            if (!$subCommand->getConfig()->isDefault()) {
                $formatsToPrint[$subCommand->getName()] = array($subCommand->getArgsFormat(), false);
            }
        }

        $appName = $command->getApplication()->getConfig()->getName();
        $prefix = count($formatsToPrint) > 1 ? '    ' : '';

        $layout->add(new Paragraph('<b>USAGE</b>'));
        $layout->beginBlock();

        foreach ($formatsToPrint as $vars) {
            $this->renderSynopsis($layout, $vars[0], $appName, $prefix, $vars[1]);
            $prefix = 'or: ';
        }

        if ($command->hasAliases()) {
            $layout->add(new EmptyLine());
            $this->renderAliases($layout, $command->getAliases());
        }

        $layout->endBlock();
        $layout->add(new EmptyLine());
    }

    /**
     * Renders the aliases of a command.
     *
     * @param BlockLayout $layout  The layout.
     * @param string[]    $aliases The aliases to render.
     */
    protected function renderAliases(BlockLayout $layout, $aliases)
    {
        $layout->add(new Paragraph('aliases: '.implode(', ', $aliases)));
    }

    /**
     * Renders the "Commands" section.
     *
     * @param BlockLayout       $layout      The layout.
     * @param CommandCollection $subCommands The sub-commands to render.
     */
    protected function renderSubCommands(BlockLayout $layout, CommandCollection $subCommands)
    {
        $layout->add(new Paragraph('<b>COMMANDS</b>'));
        $layout->beginBlock();

        $subCommands = $subCommands->toArray();
        ksort($subCommands);

        foreach ($subCommands as $subCommand) {
            $this->renderSubCommand($layout, $subCommand);
        }

        $layout->endBlock();
    }

    /**
     * Renders a sub-command in the "Commands" section.
     *
     * @param BlockLayout $layout  The layout.
     * @param Command     $command The command to render.
     */
    protected function renderSubCommand(BlockLayout $layout, Command $command)
    {
        $config = $command->getConfig();
        $description = $config->getDescription();
        $help = $config->getHelp();
        $arguments = $command->getArgsFormat()->getArguments(false);
        $options = $command->getArgsFormat()->getOptions(false);

        if ($config instanceof OptionCommandConfig) {
            if ($config->isLongNamePreferred()) {
                $preferredName = '--<u>'.$config->getLongName().'</u>';
                $alternativeName = $config->getShortName() ? '-<u>'.$config->getShortName().'</u>' : null;
            } else {
                $preferredName = '-<u>'.$config->getShortName().'</u>';
                $alternativeName = '--<u>'.$config->getLongName().'</u>';
            }

            $name = $preferredName;

            if ($alternativeName) {
                $name .= ' ('.$alternativeName.')';
            }
        } else {
            $name = '<u>'.$command->getName().'</u>';
        }

        $layout->add(new Paragraph($name));
        $layout->beginBlock();

        if ($description) {
            $this->renderSubCommandDescription($layout, $description);
        }

        if ($help) {
            $this->renderSubCommandHelp($layout, $help);
        }

        if ($arguments) {
            $this->renderSubCommandArguments($layout, $arguments);
        }

        if ($options) {
            $this->renderSubCommandOptions($layout, $options);
        }

        if (!$description && !$help && !$arguments && !$options) {
            $layout->add(new EmptyLine());
        }

        $layout->endBlock();
    }

    /**
     * Renders the description of a sub-command.
     *
     * @param BlockLayout $layout      The layout.
     * @param string      $description The description.
     */
    protected function renderSubCommandDescription(BlockLayout $layout, $description)
    {
        $layout->add(new Paragraph($description));
        $layout->add(new EmptyLine());
    }

    /**
     * Renders the help text of a sub-command.
     *
     * @param BlockLayout $layout The layout.
     * @param string      $help   The help text.
     */
    protected function renderSubCommandHelp(BlockLayout $layout, $help)
    {
        $layout->add(new Paragraph($help));
        $layout->add(new EmptyLine());
    }

    /**
     * Renders the argument descriptions of a sub-command.
     *
     * @param BlockLayout $layout    The layout.
     * @param Argument[]  $arguments The arguments.
     */
    protected function renderSubCommandArguments(BlockLayout $layout, array $arguments)
    {
        foreach ($arguments as $argument) {
            $this->renderArgument($layout, $argument);
        }

        $layout->add(new EmptyLine());
    }

    /**
     * Renders the option descriptions of a sub-command.
     *
     * @param BlockLayout $layout  The layout.
     * @param Option[]    $options The options.
     */
    protected function renderSubCommandOptions(BlockLayout $layout, array $options)
    {
        foreach ($options as $option) {
            $this->renderOption($layout, $option);
        }

        $layout->add(new EmptyLine());
    }

    /**
     * Renders the "Description" section.
     *
     * @param BlockLayout $layout The layout.
     * @param string      $help   The help text.
     */
    protected function renderDescription(BlockLayout $layout, $help)
    {
        $layout
            ->add(new Paragraph('<b>DESCRIPTION</b>'))
            ->beginBlock()
            ->add(new Paragraph($help))
            ->endBlock()
            ->add(new EmptyLine())
        ;
    }
}
