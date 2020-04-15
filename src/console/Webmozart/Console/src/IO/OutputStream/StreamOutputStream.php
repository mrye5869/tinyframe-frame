<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <55585190@qq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace og\console\Webmozart\Console\IO\OutputStream;

use og\console\Webmozart\Assert\Assert;
use og\console\Webmozart\Console\Api\IO\IOException;
use og\console\Webmozart\Console\Api\IO\OutputStream;

/**
 * An output stream that writes to a PHP stream.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <55585190@qq.com>
 */
class StreamOutputStream implements OutputStream
{
    /**
     * @var resource
     */
    private $stream;

    /**
     * Creates the stream.
     *
     * @param resource $stream A stream resource.
     */
    public function __construct($stream)
    {
        Assert::resource($stream, 'stream');

        $this->stream = $stream;
    }

    /**
     * {@inheritdoc}
     */
    public function write($string)
    {
        if (null === $this->stream) {
            throw new IOException('Cannot read from a closed input.');
        }

        if (false === fwrite($this->stream, $string)) {
            throw new IOException('Could not write stream.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        if (null === $this->stream) {
            throw new IOException('Cannot read from a closed input.');
        }

        if (false === fflush($this->stream)) {
            throw new IOException('Could not flush stream.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAnsi()
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            return false !== getenv('ANSICON') || 'ON' === getenv('ConEmuANSI');
        }

        return function_exists('posix_isatty') && @posix_isatty($this->stream);
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        if ($this->stream) {
            @fclose($this->stream);
            $this->stream = null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isClosed()
    {
        return null === $this->stream;
    }
}
