<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <55585190@qq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace og\console\Webmozart\Console\Api\Event;

use og\console\Symfony\Component\EventDispatcher\Event;
use og\console\Webmozart\Console\Api\Config\ApplicationConfig;

/**
 * Dispatched after the configuration is built.
 *
 * Use this event to add custom configuration to the application.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <55585190@qq.com>
 */
class ConfigEvent extends Event
{
    /**
     * @var ApplicationConfig
     */
    private $config;

    /**
     * Creates the event.
     *
     * @param ApplicationConfig $config The application configuration.
     */
    public function __construct(ApplicationConfig $config)
    {
        $this->config = $config;
    }

    /**
     * Returns the application configuration.
     *
     * @return ApplicationConfig The application configuration.
     */
    public function getConfig()
    {
        return $this->config;
    }
}
