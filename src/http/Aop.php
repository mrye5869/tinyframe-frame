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
namespace og\http;

use og\http\aop\Proxy;
use Closure;
class Aop
{
    /**
     * 切面对象集合
     * @var array
     */
    protected $aspects = [];

    /**
     * app对象
     * @var App
     */
    protected $app;


    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * 批量导入切入点
     * @param $aspects
     * @return $this
     */
    public function importAspect($aspects)
    {
        foreach ($aspects as $name => $aspect) {
            //添加aspect
            $this->addAspect($name, $aspect);
        }

        return $this;
    }

    /**
     * 添加切入点
     * @param $name
     * @param $aspect
     * @return $this
     */
    public function addAspect($name, $aspect)
    {

        if(!is_array($aspect)) {
            //非数组
           $aspect = [$aspect];
        }

        $this->aspects[$name] = $aspect;

        return $this;
    }

    /**
     * 删除切入点
     * @param $name
     * @return $this
     */
    public function delAspect($name)
    {
        unset($this->aspects[$name]);

        return $this;
    }

    /**
     * 调度
     * @param string $name
     * @param $instance
     * @return mixed
     * @throws \Exception
     */
    public function dispatch($name, $aspect = null)
    {
        $aspects = isset($this->aspects[$name]) ? $this->aspects[$name] : [];
        if(!empty($aspect)) {
            //添加切面
            $aspects[] = $aspect;
        }

        if(empty($aspects)) {

            throw new \Exception('aspect not exists:'.$aspect);
        }

        $proxyAspects = [];
        foreach ($aspects as $aspect) {
            //添加切面对象
            $proxyAspects[] = $this->make($aspect);
        }

        //获取执行对象
        $instance = $this->make($name);

        return $this->app->make(Proxy::class, [$instance, $proxyAspects, $this->app], true);
    }
    
    
    protected function make($abstract)
    {

        if($abstract instanceof Closure) {

            $instace = $this->app->invokeFunction($abstract);

        } elseif(is_string($abstract)) {

            $instace = $this->app->make($abstract);

        } elseif(is_callable($abstract)) {

            $instace = call_user_func($abstract);

        } else {

            $instace = $abstract;
        }

        return $instace;
    }
}