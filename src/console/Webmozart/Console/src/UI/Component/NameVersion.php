<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <55585190@qq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace og\console\Webmozart\Console\UI\Component;

use og\console\Webmozart\Console\Api\Config\ApplicationConfig;
use og\console\Webmozart\Console\Api\IO\IO;
use og\console\Webmozart\Console\UI\Component;

/**
 * Renders the name and version of an application.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <55585190@qq.com>
 */
class NameVersion implements Component
{
    private $config;

    /**
     * Creates the renderer.
     *
     * @param ApplicationConfig $config The application configuration.
     */
    public function __construct(ApplicationConfig $config)
    {
        $this->config = $config;
    }

    /**
     * Renders the name and version.
     *
     * @param IO  $io          The I/O.
     * @param int $indentation The number of spaces to indent.
     */
    public function render(IO $io, $indentation = 0)
    {
        if ($this->config->getDisplayName() && $this->config->getVersion()) {
            $paragraph = new Paragraph("{$this->config->getDisplayName()} version <c1>{$this->config->getVersion()}</c1>");
        } elseif ($this->config->getDisplayName()) {
            $paragraph = new Paragraph("{$this->config->getDisplayName()}");
        } else {
            $paragraph = new Paragraph('Console Tool');
        }

        $paragraph->render($io, $indentation);
    }
}
