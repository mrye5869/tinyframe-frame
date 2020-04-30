<?php
// +----------------------------------------------------------------------
// | zibi [ WE CAN DO IT MORE SIMPLE]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2019 http://xmzibi.com All rights reserved.
// +----------------------------------------------------------------------
// | Author：MrYe       <email：55585190@qq.com>
// +----------------------------------------------------------------------
namespace og\http;

use og\cookie\Cookie;
use og\http\filesystem\UploadedFile;
use og\session\Session;

class Request
{

    /**
     * 请求参数
     * @var array
     */
    protected  $GPC;

    /**
     * 微擎配置参数
     * @var array
     */
    protected  $W;

    /**
     * 系统参数
     * @var array
     */
    protected  $server;

    /**
     * 头部信息
     * @var array
     */
    protected  $header;

    /**
     * 文件
     * @var array
     */
    protected $files;

    /**
     * 请求方式
     * @var string
     */
    protected $method;

    /**
     * 请求内容类型
     * @var array
     */
    protected $contentType = [
        'html'  => 'text/html',
        'json'  => 'application/json',
        'xml'   => 'text/xml',
        'jsonp' => 'application/javascript',
    ];

    /**
     * session对象
     * @var Session
     */
    protected $session;

    /**
     * cookie对象
     * @var Cookie
     */
    protected $cookie;


    /**
     * 构造
     * Request constructor.
     */
    public function __construct(Session $session, Cookie $cookie)
    {
        global $_W,$_GPC;
        //微擎参数
        $this->W = &$_W;
        $this->GPC = &$_GPC;
        $this->server = &$_SERVER;

        $header = [];
        if (function_exists('apache_request_headers') && $result = apache_request_headers()) {
            $header = $result;
        } else {
            $server = $this->server();
            foreach ($server as $key => $val) {
                if (0 === strpos($key, 'HTTP_')) {
                    $key          = str_replace('_', '-', strtolower(substr($key, 5)));
                    $header[$key] = $val;
                }
            }
            if (isset($server['CONTENT_TYPE'])) {
                $header['content-type'] = $server['CONTENT_TYPE'];
            }
            if (isset($server['CONTENT_LENGTH'])) {
                $header['content-length'] = $server['CONTENT_LENGTH'];
            }
        }
        $this->header = array_change_key_case($header);
        $this->files = &$_FILES;

        $this->session = $session;
        $this->cookie = $cookie;

    }

    /**
     * 获取server参数
     *
     * @access public
     * @param  string        $name 数据名称
     * @param  string        $default 默认值
     * @return mixed
     */
    public function server($name = '', $default = null)
    {
        if (empty($name)) {
            return $this->server;
        } else {
            $name = strtoupper($name);
        }

        return isset($this->server[$name]) ? $this->server[$name] : $default;
    }

    /**
     * 获取微擎系统参数
     *
     * @param string $name
     * @param null $default
     * @return array|mixed|null
     */
    public function _W($name = '', $default = null)
    {
        if (empty($name)) {
            return $this->W;
        }
        $name = explode('.', $name);
        $value = $this->W;
        foreach ($name as $key) {
            if (isset($value[$key])) {
                $value = $value[$key];
            } else {
                $value = null;
                break;
            }
        }

        return $value !== null ? $value : $default;
    }

    /**
     * 获取请求参数
     *
     * @access protected
     * @param  string|false $name 变量名
     * @param  string|array $filter 参数过滤器
     * @param  mixed $default 默认值
     * @return mixed
     */
    public function input($name = '', $default = null, $filter = '')
    {

        if ($name === 'post') {
            return $_POST;
        } elseif ($name === 'get') {
            return $_GET;
        } else if (empty($default) && empty($name)) {
            return $this->GPC;
        }

        //获取值
        $value = isset($this->GPC[$name]) ? $this->GPC[$name] : $default;
        if(is_callable($filter)) {
            //自定义过滤
            $value = call_user_func($filter, $value);

        } elseif(method_exists($this, $filter)) {
            //request自带过滤
            $value = call_user_func([$this, $filter], $value);
        }

        return $value;
    }

    /**
     * 获取当前的Header
     *
     * @access public
     * @param  string $name header名称
     * @param  string $default 默认值
     * @return string|array
     */
    public function header($name = '', $default = null)
    {
        if ('' === $name) {
            return $this->header;
        }

        $name = str_replace('_', '-', strtolower($name));

        return isset($this->header[$name]) ? $this->header[$name] : $default;
    }

    /**
     * 获取上传的文件信息
     * @access public
     * @param  string $name 名称
     * @return null|array|UploadedFile
     */
    public function file($name = '')
    {
        $files = $this->files;
        if (!empty($files)) {

            if (strpos($name, '.')) {
                list($name, $sub) = explode('.', $name);
            }

            // 处理上传文件
            $array = $this->dealUploadFile($files, $name);

            if ('' === $name) {
                // 获取全部文件
                return $array;
            } elseif (isset($sub) && isset($array[$name][$sub])) {
                return $array[$name][$sub];
            } elseif (isset($array[$name])) {
                return $array[$name];
            }
        }
    }

    protected function dealUploadFile($files, $name)
    {
        $array = [];
        foreach ($files as $key => $file) {
            if (is_array($file['name'])) {
                $item  = [];
                $keys  = array_keys($file);
                $count = count($file['name']);

                for ($i = 0; $i < $count; $i++) {
                    if ($file['error'][$i] > 0) {
                        if ($name == $key) {
                            $this->throwUploadFileError($file['error'][$i]);
                        } else {
                            continue;
                        }
                    }

                    $temp['key'] = $key;

                    foreach ($keys as $_key) {
                        $temp[$_key] = $file[$_key][$i];
                    }

                    $item[] = new UploadedFile($temp['tmp_name'], $temp['name'], $temp['type'], $temp['error']);
                }

                $array[$key] = $item;
            } else {

                if ($file['error'] > 0) {
                    if ($key == $name) {
                        $this->throwUploadFileError($file['error']);
                    } else {
                        continue;
                    }
                }

                $array[$key] = new UploadedFile($file['tmp_name'], $file['name'], $file['type'], $file['error']);

            }
        }

        return $array;
    }

    protected function throwUploadFileError($error)
    {
        static $fileUploadErrors = [
            1 => 'upload File size exceeds the maximum value',
            2 => 'upload File size exceeds the maximum value',
            3 => 'only the portion of file is uploaded',
            4 => 'no file to uploaded',
            6 => 'upload temp dir not found',
            7 => 'file write error',
        ];

        $msg = $fileUploadErrors[$error];
        throw new \Exception($msg, $error);
    }

    /**
     * 获取请求内容类型
     * @return int|string
     */
    public function getRequestType()
    {
        $content_type = $this->header('content-type');
        if(empty($content_type)) {
            //未设置content-type，从accept解析
            $accept = $this->header('accept');
            $acceptArr = explode(',', $accept);
            $content_type = isset($acceptArr[0]) ? $acceptArr[0] : 'text/html';
        }
        $type = 'html';
        //获取真实的type
        foreach ($this->contentType as $kt => $vcontent_type) {
            if(strpos($content_type, $kt) !== false) {
                //找到退出
                $type = $kt;
                break;
            }
        }

        return $type;
    }

    /**
     * 设置或获取session
     *
     * @param $key
     * @param null $value
     * @param int $expire
     * @return bool|mixed
     */
    public function session($key, $value = '', $expire = 0)
    {
        if ($value === '') {
            return $this->session->get($key);
        }

        return $this->session->set($key, $value, $expire);
    }

    /**
     * 设置或获取cookie
     *
     * @access public
     * @param $key
     * @param null $value
     * @param int $expire
     * @return bool|mixed|null
     */
    public function cookie($key, $value = '', $expire = 0)
    {

        if ($value === '') {
            return $this->cookie->get($key);
        }

        return $this->cookie->set($key, $value, $expire);
    }

    /**
     * 是否是https
     * @return array|mixed|null
     */
    public function isHttps()
    {
        return $this->_W('ishttps');
    }

    /**
     * 获取sitescheme
     * @return array|mixed|null
     */
    public function getScheme()
    {
        return $this->_W('sitescheme');
    }

    /**
     * 获取域名
     *
     * @return null
     */
    public function getDomain()
    {
        $roots = parse_url($this->_W('siteroot'));

        return isset($roots['host']) ? $roots['host'] : null;
    }

    /**
     * 获取根路径
     *
     * @return mixed
     */
    public function getRoot($_is = true)
    {
        $siteroot = $this->_W('siteroot');

        return $_is == true ? substr($siteroot, 0, strlen($siteroot) - 1) : $siteroot;
    }

    /**
     * 获取当前页面url
     *
     * @return array|mixed|null
     */
    public function getUrl()
    {
        return $this->_W('siteurl');
    }

    /**
     * 获取当前客户端操作系统
     * @return array|mixed|null
     */
    public function getOs()
    {
        return !empty(PHP_OS) ? strtolower(PHP_OS) : 'linux';
    }

    /**
     * 获取客户端IP地址
     *
     * @access protected
     * @param  integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
     * @param  boolean $adv 是否进行高级模式获取（有可能被伪装）
     * @return mixed
     */
    public function ip($type = 0, $adv = true)
    {
        $type = $type ? 1 : 0;
        static $ip = null;

        if (null !== $ip) {
            return $ip[$type];
        }

        $httpAgentIp = 'HTTP_X_REAL_IP';

        if ($httpAgentIp && $this->server($httpAgentIp)) {
            $ip = $this->server($httpAgentIp);
        } elseif ($adv) {
            if ($this->server('HTTP_X_FORWARDED_FOR')) {
                $arr = explode(',', $this->server('HTTP_X_FORWARDED_FOR'));
                $pos = array_search('unknown', $arr);
                if (false !== $pos) {
                    unset($arr[$pos]);
                }
                $ip = trim(current($arr));
            } elseif ($this->server('HTTP_CLIENT_IP')) {
                $ip = $this->server('HTTP_CLIENT_IP');
            } elseif ($this->server('REMOTE_ADDR')) {
                $ip = $this->server('REMOTE_ADDR');
            }
        } elseif ($this->server('REMOTE_ADDR')) {
            $ip = $this->server('REMOTE_ADDR');
        }

        // IP地址类型
        $ip_mode = (strpos($ip, ':') === false) ? 'ipv4' : 'ipv6';

        // IP地址合法验证
        if (filter_var($ip, FILTER_VALIDATE_IP) !== $ip) {
            $ip = ('ipv4' === $ip_mode) ? '0.0.0.0' : '::';
        }

        // 如果是ipv4地址，则直接使用ip2long返回int类型ip；如果是ipv6地址，暂时不支持，直接返回0
        $long_ip = ('ipv4' === $ip_mode) ? sprintf("%u", ip2long($ip)) : 0;

        $ip = [$ip, $long_ip];

        return $ip[$type];
    }

    /**
     * 获取当前请求的时间
     *
     * @access protected
     * @param  bool $float 是否使用浮点类型
     * @return integer|float
     */
    public function time($float = false)
    {
        return $float ? $this->server('REQUEST_TIME_FLOAT') : $this->server('REQUEST_TIME');
    }

    /**
     * 当前的请求类型
     *
     * @access public
     * @return string
     */
    public function method()
    {
        $this->method = $this->server('REQUEST_METHOD', "GET");

        return $this->method;
    }

    /**
     * 是否为cli
     * @access public
     * @return bool
     */
    public function isCli()
    {
        return PHP_SAPI == 'cli';
    }

    /**
     * 是否为cgi
     * @access public
     * @return bool
     */
    public function isCgi()
    {
        return strpos(PHP_SAPI, 'cgi') === 0;
    }

    /**
     * 是否为GET请求
     *
     * @access protected
     * @return bool
     */
    public function isGet()
    {
        return $this->method() == 'GET' ? true : false;
    }

    /**
     * 是否为POST请求
     *
     * @access protected
     * @return bool
     */
    public function isPost()
    {
        return $this->method() == 'POST' ? true : false;
    }

    /**
     * 是否为PUT请求
     * @access public
     * @return bool
     */
    public function isPut()
    {
        return $this->method() == 'PUT';
    }

    /**
     * 是否为DELTE请求
     * @access public
     * @return bool
     */
    public function isDelete()
    {
        return $this->method() == 'DELETE';
    }

    /**
     * 是否为HEAD请求
     * @access public
     * @return bool
     */
    public function isHead()
    {
        return $this->method() == 'HEAD';
    }

    /**
     * 是否为PATCH请求
     * @access public
     * @return bool
     */
    public function isPatch()
    {
        return $this->method() == 'PATCH';
    }

    /**
     * 是否为OPTIONS请求
     * @access public
     * @return bool
     */
    public function isOptions()
    {
        return $this->method() == 'OPTIONS';
    }

    /**
     * 当前是否Ajax请求
     *
     * @access protected
     * @param  bool $ajax true 获取原始ajax请求
     * @return bool
     */
    public function isAjax()
    {
        $value = $this->server('HTTP_X_REQUESTED_WITH');

        return 'xmlhttprequest' == strtolower($value) ? true : false;
    }

    /**
     * 检测是否使用手机访问
     *
     * @return bool
     */
    public function isMobile()
    {
        if ($this->server('HTTP_VIA') && stristr($this->server('HTTP_VIA'), "wap")) {
            return true;
        } elseif ($this->server('HTTP_ACCEPT') && strpos(strtoupper($this->server('HTTP_ACCEPT')), "VND.WAP.WML")) {
            return true;
        } elseif ($this->server('HTTP_X_WAP_PROFILE') || $this->server('HTTP_PROFILE')) {
            return true;
        } elseif ($this->server('HTTP_USER_AGENT') && preg_match('/(blackberry|configuration\/cldc|hp |hp-|htc |htc_|htc-|iemobile|kindle|midp|mmp|motorola|mobile|nokia|opera mini|opera |Googlebot-Mobile|YahooSeeker\/M1A1-R2D2|android|iphone|ipod|mobi|palm|palmos|pocket|portalmmm|ppc;|smartphone|sonyericsson|sqh|spv|symbian|treo|up.browser|up.link|vodafone|windows ce|xda |xda_)/i', $this->server('HTTP_USER_AGENT'))) {
            return true;
        }

        return false;
    }

    /**
     * 检测是否使用微信浏览器访问
     *
     * @return bool
     */
    public function isWeChat()
    {
        if ($this->isMobile() && strpos($this->server('HTTP_USER_AGENT'), 'MicroMessenger') !== false) {
            return true;
        }

        return false;
    }

}