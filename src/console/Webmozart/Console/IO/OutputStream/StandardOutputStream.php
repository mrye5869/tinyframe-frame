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

/**
 * An output stream that writes to the standard output.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <55585190@qq.com>
 */
class StandardOutputStream extends StreamOutputStream
{
    /**
     * Creates the stream.
     */
    public function __construct()
    {
        // From \og\console\Symfony\Component\Console\Output\ConsoleOutput
        //
        // Returns true if current environment supports writing console output
        // to STDOUT.
        //
        // IBM iSeries (OS400) exhibits character-encoding issues when writing
        // to STDOUT and doesn't properly convert ASCII to EBCDIC, resulting in
        // garbage output.

        $stream = 'OS400' === php_uname('s') ? 'php://output' : 'php://stdout';

        parent::__construct(fopen($stream, 'w'));
    }
}
