<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <55585190@qq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace og\console\Webmozart\Console\Api\Args;

use Exception;
use RuntimeException;

/**
 * Thrown when an option cannot be added.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <55585190@qq.com>
 */
class CannotAddOptionException extends RuntimeException
{
    /**
     * Creates an exception for a duplicate option.
     *
     * @param string    $name  The option name.
     * @param Exception $cause The exception that caused this exception.
     *
     * @return static The created exception.
     */
    public static function existsAlready($name, Exception $cause = null)
    {
        return new static(sprintf(
            'An option named "%s%s" exists already.',
            strlen($name) > 1 ? '--' : '-',
            $name
        ), 0, $cause);
    }
}
