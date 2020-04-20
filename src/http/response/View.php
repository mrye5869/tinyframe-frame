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
namespace og\http\response;

use og\http\Config;
use og\http\App;
use og\http\Request;
use og\http\Response;
use og\http\Route;
use og\http\View as Views;
use og\error\HttpException;

class View extends Response
{
    /**
     * 应用对象
     * @var Request
     */
    protected $app;

    /**
     * 请求对象
     * @var Request
     */
    protected $request;

    /**
     * 请求对象
     * @var Route
     */
    protected $route;

    /**
     * 模板对象
     * @var View
     */
    protected $views;

    /**
     * 配置对象
     * @var Config
     */
    protected $config;

    /**
     * 模板数据
     * @var
     */
    protected static $vars = [];

    /**
     * 响应方式
     * @var string
     */
    protected $contentType = 'text/html';


    public function __construct(App $app, Request $request, Route $route, Views $views, Config $config, $data = '', $code = 200)
    {
        $this->app = $app;
        $this->request = $request;
        $this->route = $route;
        $this->views = $views;
        $this->config = $config;

        $this->init($data, $code);
    }

    /**
     * 处理数据
     * @access protected
     * @param  mixed $data 要处理的数据
     * @return mixed
     */
    protected function output($path)
    {
        $route = $this->route->restructureRoute($path, '/');
        //拼接资源路径
        $source =   $this->app->getModulePath()
                    .$this->config->get('app.view_dir_name', 'view') ."/"
                    .strtolower($route['module']) . "/"
                    .strtolower($route['controller']) . "/"
                    .$route['action'] . ".html";

        //编译模板路径
        $compile =  $this->app->getRootPath()
                    ."data/tpl/".strtolower($this->route->getSysModule())."/"
                    .strtolower($this->app->getModuleName()) . "/"
                    .strtolower($route['controller']) . "/"
                    .strtolower($route['action']) . ".tpl.php";

       return $this->viewCompile($source, $compile);
    }

    /**
     * 模板编译
     * @param $source
     * @param $compile
     * @return false|string
     */
    public function viewCompile($source, $compile)
    {

        if(!is_file($source)) {
            throw new HttpException(404, 'view not exists:' . $source);
        }

        $html = file_get_contents($source);
        $html = $this->views->parse($html);

        if($this->route->getSysModule() == 'web') {
            //web端
            if (!empty($this->request->_W('setting.remote.type'))) {
                $html = str_replace('</body>', "<script>$(function(){\$('img').attr('onerror', '').on('error', function(){if (!\$(this).data('check-src') && (this.src.indexOf('http://') > -1 || this.src.indexOf('https://') > -1)) {this.src = this.src.indexOf('{$this->request->_W('attachurl_local')}') == -1 ? this.src.replace('{$this->request->_W('attachurl_remote')}', '{$this->request->_W('attachurl_local')}') : this.src.replace('{$this->request->_W('attachurl_local')}', '{$this->request->_W('attachurl_remote')}');\$(this).data('check-src', true);}});});</script></body>", $html);
            }
            $html = "<?php defined('IN_IA') or exit('Access Denied');?>" . $html;

        } else {
            //app端
            $business_stat_script = "</script><script type=\"text/javascript\" src=\"{$this->request->_W('siteroot')}app/index.php?i={$this->request->_W('uniacid')}&c=utility&a=visit&do=showjs&m={$this->request->_W('current_module.name')}\">";
            if (!empty($GLOBALS['_W']['setting']['remote']['type'])) {
                $html = str_replace('</body>', "<script>var imgs = document.getElementsByTagName('img');for(var i=0, len=imgs.length; i < len; i++){imgs[i].onerror = function() {if (!this.getAttribute('check-src') && (this.src.indexOf('http://') > -1 || this.src.indexOf('https://') > -1)) {this.src = this.src.indexOf('{$this->request->_W('attachurl_local')}') == -1 ? this.src.replace('{$GLOBALS['_W']['attachurl_remote']}', '{$this->request->_W('attachurl_local')}') : this.src.replace('{$GLOBALS['_W']['attachurl_local']}', '{$this->request->_W('attachurl_remote')}');this.setAttribute('check-src', true);}}};{$business_stat_script}</script></body>", $html);

            } else {
                $html = str_replace('</body>', "<script>;{$business_stat_script}</script></body>", $html);

            }
            $html = "<?php defined('IN_IA') or exit('Access Denied');?>" . $html;

        }

        if (!is_dir(dirname($compile))) {

            og_mkdirs(dirname($compile), 0755, true);
        }

        file_put_contents($compile, $html);
        //提取数组变量
        extract(self::$vars);

        ob_start(); //打开缓冲区
        include $compile;
        $content = ob_get_contents();
        ob_clean();

        return $content;
    }

    /**
     * 模板变量赋值
     *
     * @access protected
     * @param  mixed $name 变量名
     * @param  mixed $value 变量值
     * @return $this
     */
    public function assign($name, $value = '')
    {
        if (is_array($name)) {
            self::$vars = array_merge(self::$vars, $name);
        } else {
            self::$vars[$name] = $value;
        }

        return $this;
    }
}