<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <55585190@qq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace og\console\Webmozart\Console\Process;

use RuntimeException;
use og\console\Symfony\Component\Process\ProcessUtils;

/**
 * Launches an interactive process in the foreground.
 *
 * This class is used to execute "man" and "less".
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <55585190@qq.com>
 */
class ProcessLauncher
{
    /**
     * @var bool
     */
    private $running = false;

    /**
     * @var float
     */
    private $checkInterval = 0.1;

    /**
     * Returns whether the launcher is supported on the current system.
     *
     * @return bool Whether the launcher is supported on the current system.
     */
    public function isSupported()
    {
        return function_exists('proc_open');
    }

    /**
     * Returns whether the launcher is currently running.
     *
     * @return bool Whether the launcher is running.
     */
    public function isRunning()
    {
        return $this->running;
    }

    /**
     * Returns the interval used to check whether the process is still alive.
     *
     * By default, the interval is 1 second.
     *
     * @param float $checkInterval The check interval.
     */
    public function setCheckInterval($checkInterval)
    {
        $this->checkInterval = $checkInterval;
    }

    /**
     * Launches a process in the foreground.
     *
     * @param string   $command   The command to execute.
     * @param string[] $arguments Arguments to be quoted and inserted into the
     *                            command. Each key "key" in the array should
     *                            correspond to a placeholder "%key%" in the
     *                            command.
     * @param bool     $killable  Whether the process can be killed by the user.
     *
     * @return int The exit status of the process.
     */
    public function launchProcess($command, array $arguments = array(), $killable = true)
    {
        $this->installSignalHandlers($killable);

        $exitCode = $this->run($command, $arguments);

        $this->restoreSignalHandlers($killable);

        return $exitCode;
    }

    private function installSignalHandlers($terminable = true)
    {
        if (function_exists('pcntl_signal') && !$terminable) {
            pcntl_signal(SIGTERM, SIG_IGN);
            pcntl_signal(SIGINT, SIG_IGN);
        }
    }

    private function restoreSignalHandlers($terminable = true)
    {
        if (function_exists('pcntl_signal') && !$terminable) {
            pcntl_signal(SIGTERM, SIG_DFL);
            pcntl_signal(SIGINT, SIG_DFL);
        }
    }

    private function run($command, array $arguments)
    {
        if (!function_exists('proc_open')) {
            throw new RuntimeException('The "proc_open" function is not available.');
        }

        $replacements = array();

        foreach ($arguments as $name => $value) {
            $replacements['%'.$name.'%'] = ProcessUtils::escapeArgument($value);
        }

        // Insert quoted arguments
        $command = strtr($command, $replacements);

        $dspec = array(
            0 => STDIN,
            1 => STDOUT,
            2 => STDERR,
        );

        $this->running = true;
        $proc = proc_open($command, $dspec, $pipes, null, null);

        if (is_resource($proc)) {
            while (true) {
                $status = proc_get_status($proc);

                if (!$status['running']) {
                    break;
                }

                sleep($this->checkInterval);
            }

            proc_close($proc);
        }

        $this->running = false;

        return isset($status['exitcode']) ? $status['exitcode'] : 1;
    }
}
