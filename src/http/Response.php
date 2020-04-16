<?php
// +----------------------------------------------------------------------
// | zibi [ WE CAN DO IT MORE SIMPLE]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2019 http://xmzibi.com All rights reserved.
// +----------------------------------------------------------------------
// | Author：MrYe       Email：55585190@qq.com
// +----------------------------------------------------------------------
// | 响应
// +----------------------------------------------------------------------
namespace og\http;


class Response
{

    /**
     * 原始数据
     * @var mixed
     */
    protected $data;

    /**
     * 当前contentType
     * @var string
     */
    protected $contentType = 'text/html';

    /**
     * 字符集
     * @var string
     */
    protected $charset = 'utf-8';

    /**
     * 状态码
     * @var integer
     */
    protected $code = 200;

    /**
     * header参数
     * @var array
     */
    protected $header = [];


    /**
     * 初始化
     * @access protected
     * @param  mixed  $data 输出数据
     * @param  int    $code 状态码
     */
    protected function init($data = '', $code = 200)
    {
        $this->data($data)->code($code);

        $this->contentType($this->contentType, $this->charset);
    }

    /**
     * 创建Response对象
     * @access public
     * @param  mixed  $data 输出数据
     * @param  string $type 输出类型
     * @param  int    $code 状态码
     * @return Response
     */
    public static function create($data = '', $type = 'html', $code = 200)
    {
        $class = false !== strpos($type, '\\') ? $type : 'og\\http\\response\\' . ucfirst(strtolower($type));

        return Container::getInstance()->invokeClass($class, ['data' => $data, 'code' => $code]);
    }

    /**
     * 发送数据到客户端
     * @access public
     * @return void
     * @throws \InvalidArgumentException
     */
    public function send()
    {
        // 处理输出数据
        $data = $this->getContent();

        if (!headers_sent() && !empty($this->header)) {
            // 发送状态码
            http_response_code($this->code);
            // 发送头部信息
            foreach ($this->header as $name => $val) {
                header($name . (!is_null($val) ? ':' . $val : ''));
            }
        }

        $this->sendData($data);

        if (function_exists('fastcgi_finish_request')) {
            // 提高页面响应
            fastcgi_finish_request();
        }

        //进程退出
        $this->processExit();
    }

    /**
     * 进程退出
     */
    protected function processExit()
    {
        exit();
    }

    /**
     * 输出数据
     * @access protected
     * @param string $data 要处理的数据
     * @return void
     */
    protected function sendData($data)
    {
        echo $data;
    }

    /**
     * 输出数据设置
     * @access public
     * @param  mixed $data 输出数据
     * @return $this
     */
    public function data($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * 设置响应头
     * @access public
     * @param  string|array $name  参数名
     * @param  string       $value 参数值
     * @return $this
     */
    public function header($name, $value = null)
    {
        if (is_array($name)) {
            $this->header = array_merge($this->header, $name);
        } else {
            $this->header[$name] = $value;
        }

        return $this;
    }

    /**
     * 发送HTTP状态
     * @access public
     * @param  integer $code 状态码
     * @return $this
     */
    public function code($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * LastModified
     * @access public
     * @param  string $time
     * @return $this
     */
    public function lastModified($time)
    {
        $this->header['Last-Modified'] = $time;

        return $this;
    }

    /**
     * Expires
     * @access public
     * @param  string $time
     * @return $this
     */
    public function expires($time)
    {
        $this->header['Expires'] = $time;

        return $this;
    }

    /**
     * ETag
     * @access public
     * @param  string $eTag
     * @return $this
     */
    public function eTag($eTag)
    {
        $this->header['ETag'] = $eTag;

        return $this;
    }

    /**
     * 页面缓存控制
     * @access public
     * @param  string $cache 缓存设置
     * @return $this
     */
    public function cacheControl($cache)
    {
        $this->header['Cache-control'] = $cache;

        return $this;
    }

    /**
     * 设置页面不做任何缓存
     * @access public
     * @return $this
     */
    public function noCache()
    {
        $this->header['Cache-Control'] = 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0';
        $this->header['Pragma']        = 'no-cache';

        return $this;
    }

    /**
     * 页面输出类型
     * @access public
     * @param  string $contentType 输出类型
     * @param  string $charset     输出编码
     * @return $this
     */
    public function contentType($contentType, $charset = 'utf-8')
    {
        $this->header['Content-Type'] = $contentType . '; charset=' . $charset;

        return $this;
    }

    /**
     * 处理数据
     * @access protected
     * @param  mixed $data 要处理的数据
     * @return mixed
     */
    protected function output($data)
    {
        return $data;
    }

    /**
     * 获取输出数据
     * @access public
     * @return string
     */
    public function getContent()
    {
        if (null !== $this->data) {
            $content = $this->output($this->data);

            if (null !== $content && !is_string($content) && !is_numeric($content) && !is_callable([
                    $content,
                    '__toString',
                ])
            ) {
                //无法解析时，直接输出数据
                exit_data($content);
                throw new \InvalidArgumentException(sprintf('variable type error： %s', gettype($content)));
            }

            $content = (string) $content;
        }

        return $content;
    }

}