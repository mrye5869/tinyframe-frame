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
namespace og\console\Init\Make;

use og\console\Init\Make;
use og\console\Webmozart\Console\Api\Args\Format\Argument;
use og\facade\Config;


class Command extends Make
{
    protected $type = 'Command';

    public function configure()
    {
        $this->setDescription('Create a new command class')
            ->addArgument('name', Argument::REQUIRED, 'Please enter the command class')
            ->addArgument('command', Argument::OPTIONAL, 'Please enter the command');
    }

    protected function getNamespace()
    {
        return Config::get('console.namespace');
    }

    protected function buildClass($commandNames)
    {
        $stub = file_get_contents($this->getStub());

        $command = $this->args->getArgument('command') ? $this->args->getArgument('command') : $commandNames['className'];

        return str_replace(['{%className%}', '{%command%}', '{%arg%}', '{%namespace%}'], [
            ucfirst($commandNames['className']),
            $command ,
            'arg',
            $this->getNamespace($commandNames),
        ], $stub);
    }

    protected function getPath($commandNames)
    {
        return Config::get('console.create_path'). DIRECTORY_SEPARATOR .ucfirst( $commandNames['className']). '.php';
    }

    protected function getStub()
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'command.stub';
    }
}