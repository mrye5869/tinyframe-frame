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

class Middleware extends Make
{
    protected $type = 'Middleware';

    public function configure()
    {
        $this->setDescription('Create a new middleware class')
            ->addArgument('name', Argument::REQUIRED, 'Please enter the middleware class');
    }

    protected function getNamespace()
    {
        return Config::get('middleware.namespace');
    }

    protected function getPath($commandNames)
    {
        return Config::get('middleware.create_path'). DIRECTORY_SEPARATOR . ucfirst($commandNames['className']). '.php';
    }

    protected function getStub()
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'middleware.stub';
    }
}