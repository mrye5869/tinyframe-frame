<?php
// +----------------------------------------------------------------------
// | zibi [ WE CAN DO IT MORE SIMPLE]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2020 http://xmzibi.com/ All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: MrYe    <email：55585190@qq.com>
// +----------------------------------------------------------------------
namespace og\console;

use og\console\Webmozart\Console\Config\DefaultApplicationConfig;
use og\http\App;
use og\http\Config;


class ConsoleConfig extends DefaultApplicationConfig
{
    /**
     * app对象
     * @var App
     */
    protected $app;


    /**
     * 加载命令行
     * @param App $app
     * @param Config $config
     * @throws \Exception
     */
    public function init(App $app, Config $config)
    {
        $this->app = $app;
        //添加配置文件中的命令行
        $commands = $config->get('console.commands');
        $this->addCommands($commands);
    }

    /**
     * 批量添加
     * @param array $commands
     * @throws \Exception
     */
    public function addCommands($commands = [])
    {
        foreach ($commands as $name => $command) {

            $this->addCommand($name, $command);
        }
    }

    /**
     * 添加命令行
     * @param $name
     * @param $command
     * @return Webmozart\Console\Api\Config\ApplicationConfig|Webmozart\Console\Api\Config\CommandConfig|Webmozart\Console\Api\Config\OptionCommandConfig|Webmozart\Console\Api\Config\SubCommandConfig
     * @throws \Exception
     */
    public function addCommand($name, $command)
    {

        if(empty($name) || !class_exists($command)) {

            return false;
        }
        //创建命令行对象
        $console = $this->app->make($command);
        //获取指令名称
        $name = !empty($console->getName()) ? $console->getName() : $name;
        $obj = $this
            // ...
            ->beginCommand($name)
            ->setDescription($console->getDescription())
            // ...
            ->setHandler($console);
        //获取指令参数列表
        foreach ($console->getArguments() as $argument) {
            $obj =  $obj
                ->addArgument($argument['name'], $argument['flags'], $argument['description'], $argument['default']);
        }

        //结束一个命令行
        return $obj->end();
    }
}