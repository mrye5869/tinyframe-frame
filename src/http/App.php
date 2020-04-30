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

use og\cache\Cache;
use og\cookie\Cookie;
use og\error\Error;
use og\error\HttpException;
use og\Loader;
use og\session\Session;
use ReflectionMethod;
use ReflectionClass;
use og\db\Db;


/**
 * App
 * @property  Env           $env
 * @property  Config        $config
 * @property  Cookie        $cookie
 * @property  Session       $session
 * @property  Cache         $cache
 * @property  Request       $request
 * @property  Log           $log
 * @property  Error         $error
 * @property  Route         $route
 * @property  Event         $event
 * @property  Aop           $aop
 * @property  Middleware    $middleware
 * @property  View          $view
 */
class App extends Container
{
    /**
     * 版本等级
     * @var string
     */
    protected $version = '1.0';

    /**
     * 模块根目录
     * @var null
     */
    protected $modulePath;

    /**
     * 模块名称
     * @var null
     */
    protected $moduleName;

    /**
     * 项目根目录
     * @var null
     */
    protected $rootPath;

    /**
     * 命名空间
     * @var string
     */
    protected $namespace = 'app';

    /**
     * 框架路径
     * @var string
     */
    protected $framePath;


    /**
     * 初始化
     * App constructor.
     * @param $module_path
     * @param $root_path
     * @param $module_name
     * @throws \ReflectionException
     */
    public function __construct($modulePath, $rootPath, $moduleName)
    {

        $this->setModulePath($modulePath)->setRootPath($rootPath)->setModuleName($moduleName);

        //设置容器实例
        static::setInstance($this);
        //绑定
        $this->instance(self::class, $this);
        $this->instance(Container::class, $this);
        //错误注册
        $this->error->register();

        if (is_file($this->getModulePath() . '.env')) {
            //加载env
            $this->env->load($this->getModulePath() . '.env');
        }
        //加载框架内部
        Loader::autoloadRegister();

        Db::setConfig($this->config->get('database'));

        Loader::_include($this->getFramePath() . 'common.php');

        Loader::loadFile($this->getModulePath() . $this->getNamespace());

        $this->addAop();

        $this->addEvent();

    }

    /**
     * 应用执行
     * @param string $pathinfo 路由地址
     * @param array $arguments 额外参数
     * @throws \ReflectionException
     */
    public function run($pathinfo, $arguments = [])
    {
        $this->route->setPathInfo($pathinfo)->parseRoute();
        $class = $this->getNamespace() . '\\' . $this->route->getModule() . '\\controller\\' . $this->route->getController();
        if (class_exists(trim($class)) != true) {
            throw new HttpException(404, 'controller not exists:' . $class);
        }

        $instance = $this->make($class);
        //添加中间件
        $this->addMiddleware($instance);
        //调度
        $response = $this->middleware->pipeline()->send($this->request)
            ->then(function () use ($instance) {
                $action = $this->route->getAction();
                if (is_callable([$instance, $action])) {
                    // 严格获取当前操作方法名
                    $reflect = new ReflectionMethod($instance, $action);
                    $vars = $this->request->input();

                } elseif (is_callable([$instance, $this->config->get('empty_action', '_empty')])) {
                    //空操作方法
                    $reflect = new ReflectionMethod($instance, $this->config->get('empty_action', '_empty'));
                    $vars = [];
                } else {
                    // 操作不存在
                    throw new HttpException(404, 'method not exists:' . get_class($instance) . '->' . $action . '()');
                }

                return $this->invokeReflectMethod($instance, $reflect, $vars);
            });

            if($response instanceof Response) {
                //是response对象
                $response->send();

            } else {
                //不是response对象
                $type = $this->request->getRequestType();

                Response::create($response, $type)->send();
            }

    }

    /**
     * 添加切面
     *
     */
    protected function addAop()
    {

        $aop = $this->config->get('aop');
        if(!empty($aop)) {

            $this->aop->importAspect($aop);
        }

    }

    /**
     * 添加事件
     *
     */
    protected function addEvent()
    {
        $event = $this->config->get('event');
        if (isset($event['bind'])) {
            $this->event->bind($event['bind']);
        }

        if (isset($event['listen'])) {
            $this->event->listenEvents($event['listen']);
        }

        if (isset($event['subscribe'])) {
            $this->event->subscribe($event['subscribe']);
        }

    }

    /**
     * 添加中间件
     *
     * @throws \ReflectionException
     */
    protected function addMiddleware($instance)
    {
        //路由中间件
        $route = $this->route->getModule() . '/' . $this->route->getController() . '/' . $this->route->getAction();
        $routeMiddleware = $this->config->get('middleware');

        if (isset($routeMiddleware['route'][$route])) {
            $this->middleware->import($routeMiddleware['route'][$route]);
        }

        if (isset($routeMiddleware['all'])) {
            $this->middleware->import($routeMiddleware['all']);
        }
        $class = new ReflectionClass($instance);
        $properties = $class->getProperties();
        if (!empty($properties)) {
            $classProperties = [];
            //获取属性
            foreach ($class->getProperties() as $propertie) {
                $propertie->setAccessible(true);
                $classProperties[$propertie->getName()] = $propertie->getValue($instance);
            }
            $classMiddleware = isset($classProperties['middleware']) ? $classProperties['middleware'] : [];
            $this->middleware->import($classMiddleware);
        }

    }

    /**
     * 是否为调试模式
     * @access public
     * @return bool
     */
    public function isDebug()
    {
        return $this->config->get('app.app_debug', false);
    }

    /**
     * 获取应用目录
     * @return null|string
     */
    public function setModulePath($modulePath)
    {
        $this->modulePath = $modulePath;

        return $this;
    }

    /**
     * 获取应用名称
     * @return null|string
     */
    public function setModuleName($moduleName)
    {
        $this->moduleName = $moduleName;

        return $this;
    }

    /**
     * 获取应用执行目录
     * @return string
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * 获取项目根目录
     * @return null
     */
    public function setRootPath($rootPath)
    {
        $this->rootPath = $rootPath;

        return $this;
    }

    /**
     * 获取应用目录
     * @return null|string
     */
    public function getModulePath()
    {
        return $this->modulePath;
    }

    /**
     * 获取应用名称
     * @return null|string
     */
    public function getModuleName()
    {
        return $this->moduleName;
    }

    /**
     * 获取应用执行目录
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * 获取项目根目录
     * @return null
     */
    public function getRootPath()
    {
        return $this->rootPath;
    }

    /**
     * 获取框架目录
     * @return string
     */
    public function getFramePath()
    {
        return empty($this->framePath) ?  $this->framePath = dirname(__DIR__) . '/' : $this->framePath;
    }

    /**
     * 获取应用主目录
     * @return string
     */
    public function getAppPath()
    {
        return $this->getModulePath().$this->getNamespace().'/';
    }

    /**
     * 获取框架版本
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }
}