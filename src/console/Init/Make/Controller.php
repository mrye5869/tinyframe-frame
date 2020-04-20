<?php
// +----------------------------------------------------------------------
// | zibi [ WE CAN DO IT MORE SIMPLE]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2020 http://xmzibi.com/ All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: MrYe    email：55585190@qq.com
// +----------------------------------------------------------------------
namespace og\console\Init\Make;

use og\console\Init\Make;
use og\console\Webmozart\Console\Api\Args\Format\Argument;
use og\facade\App;
use og\facade\Env;

class Controller extends Make
{
    protected $type = 'Controller';

    /**
     * 指令配置
     */
    public function configure()
    {
        $this->setDescription('Create a new resource controller class')
            ->addArgument('name', Argument::REQUIRED, 'Please enter the controller');
    }


    protected function getNamespace($commandNames)
    {
       return App::getNamespace().'\\'.(isset($commandNames['module']) ? $commandNames['module'] : 'common').'\\controller';
    }

    public function getPath($commandNames)
    {
        return Env::get('app_path').(isset($commandNames['module']) ? $commandNames['module'] : 'common').'/controller/'.ucfirst($commandNames['className']).'.php';
    }

    protected function getStub()
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'controller.stub';
    }
}