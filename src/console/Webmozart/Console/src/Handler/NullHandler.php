<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <55585190@qq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace og\console\Webmozart\Console\Handler;

use og\console\Webmozart\Console\Api\Args\Args;
use og\console\Webmozart\Console\Api\Command\Command;
use og\console\Webmozart\Console\Api\IO\IO;

/**
 * A command handler that does nothing.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <55585190@qq.com>
 */
class NullHandler
{
    /**
     * {@inheritdoc}
     */
    public function handle(Args $args, IO $io, Command $command)
    {
    }
}
