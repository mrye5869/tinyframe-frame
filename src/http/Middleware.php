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

use InvalidArgumentException;
class Middleware
{
    /**
     * 中间件执行队列
     * @var array
     */
    protected $queue = [];

    /**
     * 应用对象
     * @var App
     */
    protected $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * 导入中间件
     * @access public
     * @param array  $middlewares
     * @param string $type 中间件类型
     * @return void
     */
    public function import($middlewares = [], $type = 'route')
    {
        foreach ($middlewares as $middleware) {
            $this->add($middleware, $type);
        }
    }

    /**
     * 注册中间件
     * @access public
     * @param mixed  $middleware
     * @param string $type 中间件类型
     * @return void
     */
    public function add($middleware, $type = 'route')
    {
        $middleware = $this->buildMiddleware($middleware, $type);

        if (!empty($middleware)) {
            $this->queue[$type][] = $middleware;
            $this->queue[$type]   = array_unique($this->queue[$type], SORT_REGULAR);
        }
    }

    /**
     * 注册路由中间件
     * @access public
     * @param mixed $middleware
     * @return void
     */
    public function route($middleware)
    {
        $this->add($middleware, 'route');
    }

    /**
     * 注册控制器中间件
     * @access public
     * @param mixed $middleware
     * @return void
     */
    public function controller($middleware)
    {
        $this->add($middleware, 'controller');
    }

    /**
     * 注册中间件到开始位置
     * @access public
     * @param mixed  $middleware
     * @param string $type 中间件类型
     */
    public function unshift($middleware, $type = 'route')
    {
        $middleware = $this->buildMiddleware($middleware, $type);

        if (!empty($middleware)) {
            if (!isset($this->queue[$type])) {
                $this->queue[$type] = [];
            }

            array_unshift($this->queue[$type], $middleware);
        }
    }

    /**
     * 获取注册的中间件
     * @access public
     * @param string $type 中间件类型
     * @return array
     */
    public function all($type = 'route')
    {
        return $this->queue[$type] ? $this->queue[$type] : [];
    }

    /**
     * 调度管道
     * @access public
     * @param string $type 中间件类型
     * @return Pipeline
     */
    public function pipeline($type = 'route')
    {
        return (new Pipeline())
            ->through(array_map(function ($middleware) {
                return function ($request, $next) use ($middleware) {
                    list($call, $params) = $middleware;
                    if (is_array($call) && is_string($call[0])) {
                        $call = [$this->app->make($call[0]), $call[1]];
                    }
                    $response = call_user_func($call, $request, $next, $params);

                    return $response;
                };
            }, $this->queue[$type] ? $this->queue[$type] : []))
            ->whenException([$this, 'handleException']);
    }

    /**
     * 结束调度
     * @param Response $response
     */
    public function end(Response $response)
    {
        foreach ($this->queue as $queue) {
            foreach ($queue as $middleware) {
                list($call) = $middleware;
                if (is_array($call) && is_string($call[0])) {
                    $instance = $this->app->make($call[0]);
                    if (method_exists($instance, 'end')) {
                        $instance->end($response);
                    }
                }
            }
        }
    }

    /**
     * 异常处理
     * @param Request   $passable
     * @param Throwable $e
     * @return Response
     */
    public function handleException($passable, $e)
    {

    }

    /**
     * 解析中间件
     * @access protected
     * @param mixed  $middleware
     * @param string $type 中间件类型
     * @return array
     */
    protected function buildMiddleware($middleware, $type)
    {
        if (is_array($middleware)) {
            list($middleware, $params) = $middleware;
        }

        if ($middleware instanceof Closure) {
            return [$middleware, isset($params) ? $params : []];
        }

        if (!is_string($middleware)) {
            throw new InvalidArgumentException('The middleware is invalid');
        }

        if (is_array($middleware)) {
            $this->import($middleware, $type);
            return [];
        }

        return [[$middleware, 'handle'], isset($params) ? $params : null];
    }


}
