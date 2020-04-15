<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace og\console\Webmozart\Console\IO;

use og\console\Symfony\Component\Console\Application;
use og\console\Webmozart\Console\Api\IO\Input;
use og\console\Webmozart\Console\Api\IO\IO;
use og\console\Webmozart\Console\Api\IO\Output;
use og\console\Webmozart\Console\Formatter\AnsiFormatter;
use og\console\Webmozart\Console\Formatter\PlainFormatter;
use og\console\Webmozart\Console\IO\InputStream\StandardInputStream;
use og\console\Webmozart\Console\IO\OutputStream\ErrorOutputStream;
use og\console\Webmozart\Console\IO\OutputStream\StandardOutputStream;
use og\console\Webmozart\Console\UI\Rectangle;

/**
 * An I/O that reads from/prints to the console.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <55585190@qq.com>
 */
class ConsoleIO extends IO
{
    /**
     * Creates the I/O.
     *
     * @param Input  $input       The standard input.
     * @param Output $output      The standard output.
     * @param Output $errorOutput The error output.
     */
    public function __construct(Input $input = null, Output $output = null, Output $errorOutput = null)
    {
        if (null === $input) {
            $inputStream = new StandardInputStream();
            $input = new Input($inputStream);
        }

        if (null === $output) {
            $outputStream = new StandardOutputStream();
            $formatter = $outputStream->supportsAnsi() ? new AnsiFormatter() : new PlainFormatter();
            $output = new Output($outputStream, $formatter);
        }

        if (null === $errorOutput) {
            $errorStream = new ErrorOutputStream();
            $formatter = $errorStream->supportsAnsi() ? new AnsiFormatter() : new PlainFormatter();
            $errorOutput = new Output($errorStream, $formatter);
        }

        parent::__construct($input, $output, $errorOutput);
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultTerminalDimensions()
    {
        $application = new Application();

        list($width, $height) = $application->getTerminalDimensions();

        return new Rectangle($width ?: 80, $height ?: 20);
    }
}
