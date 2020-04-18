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

abstract class Controller
{

    /**
     * 应用对象
     *
     * @var App
     */
    protected $app;

    /**
     * 请求对象
     *
     * @var Request
     */
    protected $request;

    /**
     * 请求响应对象
     *
     * @var Response
     */
    protected $response;

    /**
     * 模板数据
     *
     * @var
     */
    protected $vars;


    /**
     * 初始化【子类不重写此方法，否则会出现变量访问不到的问题】
     *
     * Og_testModuleSite constructor.
     */
    public function __construct(App $app, Request $request, Response $response)
    {
        $this->app = $app;
        $this->request = $request;
        $this->response = $response;
        //初始化模板路径
        $this->assign('addon_root_path', '/addons/' . $this->request->_W('current_module.name') . '/');
        $this->assign('addon_static_path', '/addons/' . $this->request->_W('current_module.name') . '/static/');
        $this->assign('_W', $this->request->_W());
        //初始化
        $this->__init();
    }

    // 初始化
    protected function __init()
    {}

    /**
     * 设置模板变量
     *
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        // TODO: Implement __set() method.
        $this->vars[$name] = $value;
    }

    /**
     * 模板变量赋值
     *
     * @access protected
     * @param  mixed $name 变量名
     * @param  mixed $value 变量值
     * @return $this
     */
    protected function assign($name, $value = '')
    {
        if (is_array($name)) {
            $this->vars = array_merge($this->vars, $name);
        } else {
            $this->vars[$name] = $value;
        }

        return $this;
    }

    /**
     * 渲染模板，模块/控制器/方法(模板名称)
     *
     * @param string $template
     * @param array $vars
     */
    protected function view($template = '', $vars = [])
    {

        $this->assign($vars);

        return Response::create($template, 'View', 200)->assign($this->vars);
    }

    /**
     * 重定向
     *
     * @param $url
     * @param array $query
     */
    public function redirect($url, $query = array())
    {
        if ('' !== $url) {
            $url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : og_url($url, $query);
        }

        redirect($url);
    }

    /**
     * 操作错误跳转的快捷方法
     *
     * @access protected
     * @param  mixed $msg 提示信息
     * @param  string $url 跳转的URL地址
     * @param  mixed $data 返回的数据
     * @return void
     */
    protected function error($msg = '', $url = '', $data = null)
    {
        if ('' !== $url) {
            $url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : og_url($url);
        }

        if ($this->request->isAjax()) {
            return Response::create([
                'code'  => 0,
                'msg'   => $msg,
                'data'  => $data,
            ], 'Json', 200)->send();

        }

        message($msg, $url, 'error');
    }

    /**
     * 操作成功跳转的快捷方法
     *
     * @access protected
     * @param  mixed $msg 提示信息
     * @param  string $url 跳转的URL地址
     * @param  mixed $data 返回的数据
     * @return void
     */
    protected function success($msg = '', $url = '', $data = null)
    {
        if ('' !== $url) {
            $url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : og_url($url);
        }

        if ($this->request->isAjax()) {
            return Response::create([
                'code'  => 1,
                'msg'   => $msg,
                'data'  => $data,
            ], 'Json', 200)->send();

        }

        message($msg, $url, 'success');
    }

}