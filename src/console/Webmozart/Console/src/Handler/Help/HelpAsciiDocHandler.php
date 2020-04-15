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

use og\console\Symfony\Component\Process\ExecutableFinder;
use og\console\Webmozart\Assert\Assert;
use og\console\Webmozart\Console\Api\Args\Args;
use og\console\Webmozart\Console\Api\IO\IO;
use og\console\Webmozart\Console\Process\ProcessLauncher;

/**
 * Displays the application/command help as AsciiDoc.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <55585190@qq.com>
 */
class HelpAsciiDocHandler
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $lessBinary;

    /**
     * @var ExecutableFinder
     */
    private $executableFinder;

    /**
     * @var ProcessLauncher
     */
    private $processLauncher;

    /**
     * Creates the handler.
     *
     * @param string           $path             The path to the AsciiDoc file.
     * @param ExecutableFinder $executableFinder The finder used to find the
     *                                           "less" binary.
     * @param ProcessLauncher  $processLauncher  The launcher for executing the
     *                                           "less" binary.
     */
    public function __construct($path, ExecutableFinder $executableFinder = null, ProcessLauncher $processLauncher = null)
    {
        Assert::file($path);

        $this->path = $path;
        $this->executableFinder = $executableFinder ?: new ExecutableFinder();
        $this->processLauncher = $processLauncher ?: new ProcessLauncher();
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Args $args, IO $io)
    {
        if ($this->processLauncher->isSupported()) {
            if (!$this->lessBinary) {
                $this->lessBinary = $this->executableFinder->find('less');
            }

            if ($this->lessBinary) {
                return $this->processLauncher->launchProcess(
                    escapeshellcmd($this->lessBinary).' %path%',
                    array('path' => $this->path),
                    false
                );
            }
        }

        $io->write(file_get_contents($this->path));

        return 0;
    }

    /**
     * Returns the "less" binary used to display the AsciiDoc page.
     *
     * @return string The "less" binary or `null` if the binary is auto-detected.
     */
    public function getLessBinary()
    {
        return $this->lessBinary;
    }

    /**
     * Sets the "less" binary used to display the AsciiDoc page.
     *
     * @param string $lessBinary The "less" binary to use.
     */
    public function setLessBinary($lessBinary)
    {
        if (null !== $lessBinary) {
            Assert::string($lessBinary, 'The less binary must be a string or null. Got: %s');
            Assert::notEmpty($lessBinary, 'The less binary must not be empty.');
        }

        $this->lessBinary = $lessBinary;
    }
}
