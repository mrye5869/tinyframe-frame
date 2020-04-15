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
namespace og\http;

use og\error\HttpException;
class Route
{
    /**
     * 路由pathinfo
     * @var string
     */
    protected static $pathinfo;
    /**
     * 应用对象
     * @var App
     */
    protected $app;
    /**
     * 应用对象
     * @var Config
     */
    protected $config;
    /**
     * 请求对象
     * @var Request
     */
    protected $request;
    /**
     * 系统真实模块
     * @var string
     */
    protected $sysModule;

    /**
     * 模块
     * @var string
     */
    protected $module;

    /**
     * 控制器
     * @var string
     */
    protected $controller;

    /**
     * 方法
     * @var string
     */
    protected $action;


    /**
     * Route constructor.
     * @param Request $request
     * @param Config $config
     */
    public function __construct(App $app, Request $request, Config $config)
    {
        $this->app = $app;
        $this->request = $request;
        $this->config = $config;
    }

    /**
     * 解析路由
     *
     * @return $this
     */
    public function parseRoute()
    {
        $delimiter = $this->config->get('app.pathinfo_depr');
        $routearr = explode($delimiter, self::$pathinfo);
        $module_arr = preg_split('/(?=[A-Z])/', $routearr[0], -1, PREG_SPLIT_NO_EMPTY);
        //完整路由:doMobileMobile.index.a | 非完整路由，省略模块:doMobileIndex.a
        if(count($routearr) == 3) {
            //模块
            $module = isset($module_arr[2]) ? $module_arr[2] : $module_arr[1];
            //控制器
            $controller = isset($routearr[1]) ? $routearr[1] : $this->config->get('app.default_controller');
            //方法
            $action = isset($routearr[2]) ? $routearr[2] : $this->config->get('app.default_action');

        } else {
            //模块
            $module = strtolower($module_arr[1]);
            //控制器
            $controllerArr = array_slice($module_arr, 2 , count($module_arr) -2);
            $controller = implode('', $controllerArr);
            //方法
            $action = isset($routearr[1]) ? $routearr[1] : $this->config->get('app.default_action');
        }
        $this->setModule($module);
        $this->setController($controller);
        $this->setAction($action);

        return $this;
    }

    /**
     * 生成url
     *
     * @param $url
     * @param array $query
     * @param bool $isdomain
     * @return bool|string
     */
    public function url($url, $query = array(), $isdomain = false)
    {

        $delimiter = $this->config->get('app.pathinfo_depr');
        //根据url重新解析路由
        $route = $this->restructureRoute($url, '/');
        $query['do'] = $route['module']. $delimiter .$route['controller'] . $delimiter . $route['action'];
        $query['m'] = isset($query['m']) ? $query['m'] : $this->app->getModuleName();

        if ($route['sys_module'] == 'web') {
            $url = str_replace('./', '/web/', wurl('site/entry', $query));
        } else {
            $url = str_replace('./', '/app/', murl('entry', $query));
        }
        $domain = '';
        if($isdomain === true) {
            $domain = $this->request->getRoot();
        } elseif(!empty($isdomain)) {
            $domain = strpos($isdomain, '://') === false ? $this->request->_W('sitescheme').$isdomain : $isdomain;
        }

        return $domain . $url;
    }

    /**
     * 返回重构的路由
     *
     * @access privates
     * @param $name
     * @param string $mode
     * @return array
     */
    public function restructureRoute($name, $mode = ':')
    {
        $routeArr = explode($mode, $name);
        if (count($routeArr) == 3) {
            //完整
            list($module, $controller, $action) = $routeArr;
        } elseif (count($routeArr) == 2) {
            //省略模块
            list($controller, $action) = $routeArr;
        } elseif (!empty($routeArr[0])) {
            //省略模块和控制器
            list($action) = $routeArr;
        }

        return [
            'module'        => isset($module) ? strtolower($module) : $this->getModule(),
            'controller'    => isset($controller) ? ucfirst($controller) : $this->getController(),
            'action'        => isset($action) ? $action : $this->getAction(),
        ];
    }

    /**
     * @return mixed
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * 设置模块
     *
     * @param $module
     * @return $this
     */
    public function setModule($module)
    {
        if(!$this->checkRoute($module)) {
            throw new HttpException(500, 'module error:'.$module);
        }

        $this->module = strtolower($module);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * 设置控制器
     *
     * @param $controller
     * @return $this
     */
    public function setController($controller)
    {
        if(!$this->checkRoute($controller)) {
            throw new HttpException(500, 'controller error:'.$controller);
        }

        $this->controller = ucfirst($controller);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * 设置方法
     *
     * @param $action
     * @return $this
     */
    public function setAction($action)
    {
        if(!$this->checkRoute($action)) {
            throw new HttpException(500, 'action error:'.$action);
        }

        $this->action = $action;

        return $this;
    }

    /**
     * 设置pathinfo
     *
     * @param $pathinfo
     * @return $this
     */
    public function setPathInfo($pathinfo)
    {
        self::$pathinfo = $pathinfo;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSysModule()
    {
        if(empty($this->sysModule)) {
            //查找真实的sysModule
            $script_name = $this->request->server('SCRIPT_NAME');
            $script_name_arr = explode('/', $script_name);
            $this->sysModule = isset($script_name_arr[1]) ? $script_name_arr[1] : 'web';
            if($this->sysModule != 'web') {
                $this->sysModule = 'mobile';
            }
        }

        return $this->sysModule;
    }

    /**
     * @param mixed $sysModule
     * @return $this
     */
    public function setSysModule($sysModule)
    {
        $this->sysModule = strtolower($sysModule);

        return $this;
    }

    /**
     * 检查【模块、控制器、方法是否正常】
     *
     * @param $name
     * @return bool
     */
    protected function checkRoute($name)
    {
        if(preg_match('/^[A-Za-z0-9-_\.]+/', $name)) {
            return true;
        }

        return false;
    }

}