<?php
// +----------------------------------------------------------------------
// | zibi [ WE CAN DO IT MORE SIMPLE]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2020 http://xmzibi.com/ All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: MrYe    email:55585190@qq.com
// +----------------------------------------------------------------------
namespace og\console\Init\Frame;

use og\console\Command;
use og\console\Webmozart\Console\Api\Args\Args;
use og\console\Webmozart\Console\Api\Args\Format\Argument;
use og\facade\App;

class FrameInit extends Command
{

    /**
     * 指令配置
     */
    public function configure()
    {
        $this->setDescription('Create a new project')
            ->addArgument('moduleName', Argument::OPTIONAL, 'Please enter the identifie');
    }

    /**
     * 处理
     * @param Args $args
     */
    public function handle(Args $args)
    {
        if(!$this->testWrite(App::getRootPath().'data')) {
            $this->showMessage( 'data directory does not have permission!', 'error');
            return false;
        }

        if(!$this->testWrite(App::getModulePath())) {
            $this->showMessage( 'project directory does not have permission!', 'error');
            return false;
        }

        if(is_file(App::getModulePath().'site.php')) {
            $this->showMessage( 'project already exists!', 'error');
            return false;
        }
        //获取初始化的名称
        $moduleName = $args->getArgument('moduleName');
        if(empty($moduleName) || !preg_match('/^[a-zA-Z][a-zA-z0-9]*_[a-zA-z0-9]+$/', $moduleName)) {
            $moduleName = $this->getModuleName();
        }
        //获取插件名称
        $addon_name = $this->getAddonName();
        //获取version
        $version = $this->getVersion();
        //其它
        $type = trim($this->input('Please enter the module type:'));
        $ability = trim($this->input('Please enter the module ability:'));
        $description = trim($this->input('Please enter the module description:'));
        $author = trim($this->input('Please enter the module author:'));
        $url = trim($this->input('Please enter the module url:'));
        //替换
        $replaces = [
            '{%module_class%}'    => $this->parseModuleName($moduleName),
            '{%addon_name%}'      => $addon_name,
            '{%module_name%}'     => $moduleName,
            '{%version%}'         => $version,
            '{%type%}'            => $type,
            '{%ability%}'         => $ability,
            '{%description%}'     => $description,
            '{%author%}'          => $author,
            '{%url%}'             => $url,
        ];
        //批量创建
        foreach (['base', 'manifest', 'module', 'processor', 'receiver', 'site', 'wxapp', 'tinyframe'] as $file) {
            $this->createFile($file, $replaces);
        }

        //成功
        $this->showMessage('TinyFrame init successfully.', 'info');
    }

    /**
     * 创建文件
     * @param $file
     * @param $replaces
     * @return bool|int
     */
    protected function createFile($file, $replaces)
    {

        $stubPath = __DIR__.'/stubs/'.$file.'.stub';
        if(!is_file($stubPath)) {
            return false;
        }
        //获取内容并替换
        $content = file_get_contents($stubPath);
        foreach ($replaces as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }

        switch ($file)
        {
            case 'manifest':
                $status = file_put_contents(App::getModulePath().'manifest.xml', $content);
                break;
            case 'tinyframe':
                $status = file_put_contents(App::getModulePath().'tinyframe', $content);
                break;
            default:
                $status = file_put_contents(App::getModulePath().$file.'.php', $content);
                break;
        }

        return $status;
    }

    /**
     * 解析模块名称
     * @param $moduleName
     * @return string
     */
    protected function parseModuleName($moduleName)
    {
        $moduleNameArr = explode('_', $moduleName);
        $names = [];
        foreach ($moduleNameArr as $name) {
            $names[] = ucfirst($name);
        }

        return implode('_', $names);
    }

    /**
     * 获取正确的模块标识
     * @return string
     */
    protected function getModuleName()
    {
        $moduleName = trim($this->input('Please enter the identifie:'));
        //验证是否合法
        if(!preg_match('/^[a-zA-Z][a-zA-z0-9]*_[a-zA-z0-9]+$/', $moduleName)) {
            //不合法
            $this->showMessage('Illegal module name!', 'error');
            return $this->getModuleName();
        }

        return $moduleName;
    }

    /**
     * 获取插件名称
     * @return string
     */
    protected function getAddonName()
    {
        $addonName = trim($this->input('Please enter the addon name:'));
        //验证是否合法
        if(empty($addonName)) {
            //不合法
            $this->showMessage('Illegal addon name!', 'error');
            return $this->getAddonName();
        }

        return $addonName;
    }

    /**
     * 获取模块等级
     * @return string
     */
    protected function getVersion()
    {
        $version = trim($this->input('Please enter the module version:'));
        //验证是否合法
        if(!is_numeric($version)) {
            //不合法
            $this->showMessage('Illegal module version!', 'error');
            return $this->getVersion();
        }

        return $version;
    }

    /**
     * 测试读写
     *
     * @param string $dir
     *
     * @return bool
     */
    protected function testWrite($dir)
    {

        $tfile = "_test.txt";
        $fp = @fopen($dir . "/" . $tfile, "w");
        if (!$fp) {
            return false;
        }
        fclose($fp);
        $rs = @unlink($dir . "/" . $tfile);
        if ($rs) {
            return true;
        }

        return false;
    }
}