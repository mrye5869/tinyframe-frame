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
 * An output stream that writes to the error output.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <55585190@qq.com>
 */
class ErrorOutputStream extends StreamOutputStream
{
    /**
     * Creates the stream.
     */
    public function __construct()
    {
        parent::__construct(fopen('php://stderr', 'w'));
    }
}
