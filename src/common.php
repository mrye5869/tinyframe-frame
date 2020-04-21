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

use og\http\Container;
use og\facade\Event;
use og\facade\Env;
use og\facade\Cache;
use og\facade\Config;
use og\http\Response;
use og\Loader;

if (!function_exists('exit_data')) {
    /**
     * 输出调试信息
     *
     * @param mixed $data
     * @param string $type
     * @param void
     */
    function exit_data($data, $type = 'pr', $exit = true)
    {
        switch ($type) {
            case 'pr':
                $func = 'print_r';
                break;
            case 'vd':
                $func = 'var_dump';
                break;
            default:
                $func = function_exists($type) ? $type : 'print_r';
                break;
        }
        if (is_array($data) || is_object($data)) {
            echo '<pre>';
        }
        call_user_func($func, $data);
        if ($exit)
            exit();
    }
}

if (!function_exists('container')) {
    /**
     * 快速获取容器中的实例 支持依赖注入
     *
     * @param string $name 类名或标识 默认获取当前应用实例
     * @param array $args 参数
     * @param bool $newInstance 是否每次创建新的实例
     * @return mixed
     */
    function container($name = '', $args = [], $newInstance = false)
    {
        return Container::get($name, $args, $newInstance);
    }
}

if (!function_exists('invoke')) {
    /**
     * 调用反射实例化对象或者执行方法 支持依赖注入
     *
     * @param mixed $call 类名或者callable
     * @param array $args 参数
     * @return mixed
     */
    function invoke($call, array $args = [])
    {
        if (is_callable($call)) {
            return Container::getInstance()->invoke($call, $args);
        }

        return Container::getInstance()->invokeClass($call, $args);
    }
}

if(!function_exists('app')) {
    /**
     * 获取当前App对象实例
     *
     * @return \og\http\App
     */
    function app()
    {
        return container('og\http\App');
    }
}

if (!function_exists('request')) {
    /**
     * 获取当前Request对象实例
     *
     * @return \og\http\Request
     */
    function request()
    {
        return container('request');
    }
}

if (!function_exists('route')) {
    /**
     * 获取当前Route对象实例
     *
     * @return \og\http\Route
     */
    function route()
    {
        return container('route');
    }
}

if (!function_exists('event')) {
    /**
     * 触发事件
     *
     * @param mixed $event 事件名（或者类名）
     * @param mixed $args  参数
     * @param bool  $once  是否返回第一个值
     * @return mixed
     */
    function event($event, $args, $once = false)
    {
        return Event::trigger($event, $args, $once);
    }
}

if (!function_exists('cookie')) {
    /**
     * 设置或获取cookie
     *
     * @access protected
     * @param $key
     * @param null $value
     * @param int $expire
     * @return bool|mixed|null
     */
    function cookie($key, $value = '', $expire = 0)
    {
        return request()->cookie($key, $value, $expire);
    }
}

if (!function_exists('session')) {
    /**
     * 设置或获取session
     *
     * @param $key
     * @param null $value
     * @param int $expire
     * @return bool|mixed
     */
    function session($key, $value = '', $expire = 0)
    {
        return request()->session($key, $value, $expire);
    }
}

if (!function_exists('input')) {
    /**
     * 获取请求参数
     *
     * @param string $name  变量名
     * @param null $default 默认值
     * @param string $filter 参数过滤器
     * @return mixed
     */
    function input($name = '', $default = null, $filter = '')
    {
        return request()->input($name, $default, $filter);
    }
}

if (!function_exists('W')) {
    /**
     *获取微擎系统参数
     *
     * @param string $name
     * @param null $default
     * @return array|mixed|null
     */
    function W($name = '', $default = null)
    {
        return request()->_W($name, $default);
    }
}

if (!function_exists('env')) {
    /**
     * 获取配置参数
     *
     * @param string $name
     * @param null $default
     * @return array|mixed|null
     */
    function env($name = '', $default = null)
    {
        return Env::get($name, $default);
    }

}

if (!function_exists('config')) {
    /**
     * 获取应用配置参数
     *
     * @param string $name
     * @param null $default
     * @return array|mixed|null
     */
    function config($name = '', $default = null)
    {
        return Config::get($name, $default);
    }

}

if (!function_exists('cache')) {
    /**
     * 缓存操作
     *
     * @param string $key
     * @param string $value
     * @param int    $expire
     * @return array|mixed|null
     */
    function cache($key, $value = '', $expire = 0)
    {
        if ($value === '') {
            return Cache::get($key);
        }

        return Cache::set($key, $value, $expire);
    }
}

if (!function_exists('og_url')) {
    /**
     * 生成url
     *
     * @param $url
     * @param array $query
     * @param bool $isdomain
     * @return bool|string
     */
    function og_url($url, $query = array(), $isdomain = false)
    {
        return route()->url($url, $query, $isdomain);
    }
}

if (!function_exists('redirect')) {
    /**
     * 重定向
     *
     * @param $url
     */
    function redirect($url)
    {
        return Response::create($url, 'Redirect', 200)->send();
    }
}

if (!function_exists('view')) {
    /**
     * 渲染视图
     *
     * @param string $template
     * @param array $vars
     * @return mixed
     */
    function view($template = '', $vars = [])
    {
        return Response::create($template, 'View')->assign($vars);
    }
}

if (!function_exists('view_content')) {
    /**
     * 获取视图内容
     *
     * @param string $template
     * @return string
     */
    function view_content($template = '')
    {
        return Response::create($template, 'View')->getContent();
    }
}

if (!function_exists('json')) {
    /**
     * 返回json数据
     *
     * @param mixed $data    返回的数据
     * @param int   $code    状态码
     * @param array $header  头部
     * @param array $options 参数
     * @return void
     */
    function json($data = [], $code = 200, $header = [], $options = [])
    {
        return Response::create($data, 'json', $code)->header($header)->options($options);
    }
}

if (!function_exists('xml')) {
    /**
     * 返回xml数据
     *
     * @param mixed $data    返回的数据
     * @param int   $code    状态码
     * @param array $header  头部
     * @param array $options 参数
     * @return void
     */
    function xml($data = [], $code = 200, $header = [], $options = [])
    {
        return Response::create($data, 'Xml', $code)->header($header)->options($options);
    }
}

if(!function_exists('is_debug')) {
    /**
     * 是否是调试模式
     *
     * @return array|mixed|null
     */
    function is_debug()
    {
        return config('app_debug', true);
    }
}

if (!function_exists('og_mkdirs')) {
    /**
     * 创建多级目录
     *
     * @param $dir
     * @return bool
     */
    function og_mkdirs($dir)
    {
        return is_dir($dir) or og_mkdirs(dirname($dir)) and mkdir($dir, 0777);
    }
}

if (!function_exists('og_rmdirs')) {
    /**
     * 删除文件夹
     *
     * @param string
     * @param int
     */
    function og_rmdirs($dir, $time_thres = -1)
    {
        foreach (Loader::listFile($dir) as $f) {
            if ($f ['isDir']) {
                og_rmdirs($f ['pathname'] . '/');
            } elseif ($f ['isFile'] && $f ['filename']) {
                if ($time_thres == -1 || $f ['mtime'] < $time_thres) {
                    @unlink($f ['pathname']);
                }
            }
        }
    }
}



