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


use og\console\Webmozart\Console\ConsoleApplication;
use og\http\App;

class Console
{

    /**
     * 名称
     * @var string
     */
    protected static $name = 'tinyFrame';

    /**
     * 版本号
     * @var float
     */
    protected static $vension = '0.1';

    /**
     * 系统命令
     * @var array
     */
    protected static $initCommands = [
        'init'              => 'og\console\Init\Frame\FrameInit',
        'make-controller'   => 'og\console\Init\Make\Controller',
        'make-command'      => 'og\console\Init\Make\Command',
        'make-event'        => 'og\console\Init\Make\Event',
        'make-middleware'   => 'og\console\Init\Make\Middleware',
        'make-model'        => 'og\console\Init\Make\Model',
    ];

    /**
     * 启动
     * @param App $app
     * @return int
     * @throws \Exception
     */
    public static function run(App $app)
    {
        //添加配置文件命令行
        $applicationConfig = $app->make(ConsoleConfig::class, [self::$name, self::$vension]);
        $app->invoke([$applicationConfig, 'init']);
        //添加系统命令行
        $applicationConfig->addCommands(self::$initCommands);
        //创建对象
        $client = new ConsoleApplication($applicationConfig);

        return $client->run();
    }


}