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
namespace og\console\Init;

use og\console\Command;
use og\facade\App;
use og\facade\Env;

abstract class Make extends Command
{
    /**
     * 类型
     * @var string
     */
    protected $type;

    abstract protected function getStub();

    /**
     * 处理
     * @return bool|void
     */
    public function handle()
    {
        //获取command命令
        $commandName = $this->getCommandName();
        //解析指令
        $commandNames = $this->parseName($commandName);
        $className = $commandNames['className'];
        if(!preg_match('/^[A-Za-z0-9-_\.]+/', $className)) {
            $this->showMessage('Illegal class name! ', 'error');
            die();
        }

        $path = $this->getPath($commandNames);
        if (is_file($path)) {
            $this->showMessage( $this->type . ' already exists!', 'error');
            return false;
        }

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, $this->buildClass($commandNames));

        $this->showMessage($this->type . ' created successfully.', 'info');

    }

    /**
     * 绑定class
     * @param $className
     * @return mixed
     */
    protected function buildClass($commandNames)
    {
        $stub = file_get_contents($this->getStub());

        return str_replace(['{%className%}', '{%namespace%}'], [
            ucfirst($commandNames['className']),
            $this->getNamespace($commandNames),
        ], $stub);
    }

    /**
     * 获取指令
     * @return mixed
     */
    protected function getCommandName()
    {
        return $this->args->getArgument('name');
    }

    /**
     * 获取类路径
     * @param $name
     * @return string
     */
    protected function getPath($commandNames)
    {
        return Env::get('app_path') . implode('/', $commandNames) . '.php';
    }

    /**
     * 获取类命名空间
     * @param string $module
     * @return string
     */
    protected function getNamespace($commandNames)
    {
        return $commandNames['module'] ? (App::getNamespace() . '\\' . $commandNames['module']) : App::getNamespace();
    }

    /**
     * 获取模块命名空间
     * @param $className
     * @return string
     */
    protected function parseName($className)
    {

        if(strpos($className, '/') !== false) {
            $delimiter = '/';

        } elseif(strpos($className, '-') !== false) {
            $delimiter = '-';

        } elseif(strpos($className, ':') !== false) {
            $delimiter = ':';

        }

        if(!empty($delimiter)) {
            //解析
            $moduleArr = explode($delimiter, $className);
            return [
                'module'    => $moduleArr[0],
                'className' => $moduleArr[1],
            ];

        } else {

            return [
                'className' => $className,
                'module'    => 'common',
            ];
        }

    }


}