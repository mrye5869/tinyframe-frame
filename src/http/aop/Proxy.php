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
namespace og\http\aop;

use og\http\App;
use \ReflectionClass;
use \ReflectionMethod;
class Proxy
{
    /**
     *before
     */
    const before = "before";

    /**
     * after
     */
    const after = "after";

    /**
     * 执行者
     * @var object
     */
    protected $instance;

    /**
     * 切入点
     * @var Aspect
     */
    protected $aspects;

    /**
     * 应用对象
     * @var App
     */
    protected $app;


    public function __construct($instance, $aspects, App $app)
    {
        $this->instance = $instance;
        $this->aspects = $aspects;
        $this->app = $app;
    }

    /**
     * 调度
     * @param $name
     * @param $arguments
     * @return mixed
     * @throws \ReflectionException
     */
    public function __call($name, $arguments)
    {

        $reflect = new ReflectionMethod($this->instance, $name);

        // 执行前判断
        $this->invokeAdvice($name, Proxy::before);

        //执行
        $result = $this->app->invokeReflectMethod($this->instance, $reflect, $arguments);

        // 执行后判断
        $this->invokeAdvice($name, Proxy::after);

        return $result;
    }

    /**
     * 执行前后操作方法
     * @param $name
     * @param $point
     * @return bool
     * @throws \ReflectionException
     */
    protected function invokeAdvice($advice, $point)
    {

        foreach ($this->aspects as $aspect) {
            //判断是否执行
            if(!$aspect->isJoinPoint($advice)) {
                //跳出循环
                continue;
            }
            $asp = new ReflectionClass($aspect);
            // 获取所有 public 方法
            $aspMethods = $asp->getMethods(ReflectionMethod::IS_PUBLIC);

            $result = [];
            foreach ($aspMethods as $aspMethod) {
                if(strpos($aspMethod->getName(), $point) !== false) {

                    // 执行 aspect 方法
                    $result[get_class($aspect)][] = $this->app->invokeReflectMethod($aspect, $aspMethod);

                }
            }
        }


        return  $result;
    }
}