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


abstract class Aspect
{
    /**
     * 切点
     * @var array
     */
    protected $joinPoint;

    /**
     * 添加切点
     * @param string $joinpoint
     * @return $this
     */
    public function addPoint($joinpoint)
    {
        $this->joinPoint[] = $joinpoint;

        return $this;
    }

    /**
     * 判断当前调用的方法是否满足切点
     * @param $methodName
     * @return bool
     */
    public function isJoinPoint($methodName)
    {
        return in_array($methodName, $this->joinPoint);
    }
}