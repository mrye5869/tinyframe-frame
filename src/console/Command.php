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
namespace og\console;

use og\console\Webmozart\Console\Api\Args\Args;
use og\console\Webmozart\Console\Api\IO\IO;
use og\console\Webmozart\Console\Api\Command\Command as WCommand;

abstract class Command
{
    /**
     * Args对象
     * @var Args
     */
    protected $args;

    /**
     * IO对象
     * @var IO
     */
    protected $io;

    /**
     * Command对象
     * @var Command
     */
    protected $command;

    /**
     * 指令参数列表
     * @var array
     */
    protected $arguments = [];

    /**
     * 指令名称
     * @var string
     */
    protected $name;

    /**
     * 指令描述
     * @var string
     */
    protected $description;


    public function __construct()
    {
        $this->configure();
    }

    /**
     * 初始化
     * @param Args $args
     * @param IO $io
     * @param Command $command
     */
    public function init(Args $args, IO $io, WCommand $command)
    {
        $this->args = $args;
        $this->io = $io;
        $this->command = $command;
    }

    /**
     * 设置配置
     */
    protected function configure()
    {}

    /**
     * 处理事件
     */
    protected function handle()
    {}

    /**
     * 添加参数
     * @param string $name        名称
     * @param int    $mode        类型
     * @param string $description 描述
     * @param mixed  $default     默认值
     * @return Command
     */
    public function addArgument($name, $flags = 0, $description = null, $default = null)
    {
        $this->arguments[] = [
            'name'          => $name,
            'flags'         => $flags,
            'description'   => $description,
            'default'       => $default,
        ];

        return $this;
    }

    /**
     * 设置指令名称
     * @param string $name
     * @return Command
     * @throws \InvalidArgumentException
     */
    public function setName($name)
    {
        $this->validateName($name);

        $this->name = $name;

        return $this;
    }

    /**
     * 设置指令描述
     * @param $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * 读取输入表单
     * @param $message
     * @param $color
     * @return string
     */
    protected function input($message, $color = '')
    {
        $this->showMessage($message, $color);

        return $this->io->readLine();
    }

    /**
     * 显示信息
     * @param $message
     * @param string $color b:加粗、u：添加下划线、bu：加粗和下划线、c2：黄色、warn：黑色、error：错误颜色，红色、info:蓝色
     */
    protected function showMessage($message, $color = '')
    {
        if($color != '') {
            $message = '<'.$color.'>'.$message.'</'.$color.'>';
        }

        return $this->io->writeLine($message);
    }

    /**
     * 验证指令名称
     * @param string $name
     * @throws \InvalidArgumentException
     */
    private function validateName($name)
    {
        if (!preg_match('/^[^\:]++(\:[^\:]++)*$/', $name)) {
            throw new \InvalidArgumentException(sprintf('Command name "%s" is invalid.', $name));
        }
    }

    /**
     * 获取指令名称
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * 获取指令参数列表
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * 获取指令描述
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
}