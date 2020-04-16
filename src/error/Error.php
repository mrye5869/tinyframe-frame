<?php
// +----------------------------------------------------------------------
// | zibi [ WE CAN DO IT MORE SIMPLE]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2020 http://xmzibi.com/ All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: mrye  55585190@qq.com
// +----------------------------------------------------------------------
namespace og\error;

use og\http\App;
use og\http\Log;
use og\http\Request;
use og\http\Response;


class Error
{
    /**
     * 应用对象
     * @var App
     */
    protected $app;

    protected $request;

    /**
     * 应用对象
     * @var Log
     */
    protected $log;

    /**
     * 错误信息
     *
     * @var array
     */
    protected static $errorOn = [
        1   => 'E_ERROR',
        2   => 'E_WARNING',
        8   => 'E_NOTICE',
        16  => 'E_CORE_ERROR',
        256 => 'E_USER_ERROR',
        512 => 'E_USER_WARNING',
        1024=> 'E_USER_NOTICE',
    ];

    /**
     * 构造
     *
     * Error constructor.
     * @param App $app
     * @param Log $log
     */
    public function __construct(App $app,Request $request, Log $log)
    {
        $this->app = $app;
        $this->request = $request;
        $this->log = $log;
        $this->register();
    }

    /**
     * 注册异常处理
     *
     * @access public
     * @return void
     */
    protected function register()
    {
        if($this->app->isDebug()) {
            error_reporting(E_ALL ^ E_NOTICE);
        } else {
            error_reporting(0);
        }
        set_error_handler([$this, 'appError']);
        set_exception_handler([$this, 'appException']);
        register_shutdown_function([$this, 'appShutdown']);
    }

    /**
     * 捕获异常
     *
     * Exception Handler
     * @access public
     * @param  \Exception|\Throwable $e
     */
    public function appException($e)
    {
        if (!is_object($e)) {
            return true;
        }
        $this->showError([
            'errno'     => 'Exception('.self::getErrno($e->getCode()).')',
            'msg'       => $e->getMessage(),
            'errfile'   => $e->getFile(),
            'errline'   => $e->getLine(),
        ]);
    }

    /**
     * 捕获普通错误
     *
     * Error Handler
     * @access public
     * @param  integer $errno   错误编号
     * @param  integer $errstr  详细错误信息
     * @param  string  $errfile 出错的文件
     * @param  integer $errline 出错行号
     * @throws ErrorException
     */
    public function appError($errno, $errstr, $errfile = '', $errline = 0)
    {
        if(!self::isHandle($errno)) {
            return true;
        }
        $this->showError([
            'errno'     => self::getErrno($errno),
            'msg'       => $errstr,
            'errfile'   => $errfile,
            'errline'   => $errline,
        ]);
    }

    /**
     * 应用结束
     *
     * Shutdown Handler
     * @access public
     */
    public function appShutdown()
    {
        $last_error = error_get_last();
        if(isset($last_error['type']) && !self::isHandle($last_error['type'])) {
            return true;
        }

        $this->log->error(error_get_last());
    }

    /**
     * 确定错误类型是否致命
     *
     * @access protected
     * @param  int $type
     * @return bool
     */
    protected static function isFatal($type)
    {
        return in_array($type, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE]);
    }

    /**
     * 错误类型是否需要处理
     *
     * @param $type
     * @return bool
     */
    protected static function isHandle($type)
    {
        if(in_array($type, array(E_NOTICE, E_WARNING))) {
            //这个错误级别不做处理
            return false;
        }
        return true;
    }

    /**
     * 获取对应的错误类型
     *
     * @param $code
     * @return mixed
     */
    protected static function getErrno($code)
    {
        return isset(self::$errorOn[$code]) ? self::$errorOn[$code] : $code;
    }

    /**
     * 展示错误信息
     *
     * @param array $errorMsg
     * @param bool $die
     */
    protected function showError($errorMsg = [])
    {
        if($this->app->isDebug()) {

            if(PHP_SAPI == 'cli') {
                $tips = PHP_EOL;
                foreach ($errorMsg as $key => $val) {
                    if(is_array($val) || is_object($val)) {
                        $val = var_export($val, true);
                    }
                    $tips .= $key.':'.$val.PHP_EOL;
                }
                //命令行输出
                $this->cliErrorOut($tips);

            } else {
                //展示错误信
                $tips = '';
                foreach ($errorMsg as $key => $val) {
                    if(is_array($val) || is_object($val)) {
                        $val = var_export($val, true);
                    }
                    $tips .= $key.'：<span style="color: red;">'.$val.'</span><hr/>';
                }
                $errors = '<title>页面发生错误</title><body><div style="width: 50%;margin: 0 auto;margin-top: 100px;"><h2 style="text-align: center;">错误信息：</h2>'.$tips.'</div></body>';
                //错误响应
                return Response::create($errors, 'html', 500)->send();
            }

        } else {
            //写日志
            $this->log->error($errorMsg);

        }
    }

    /**
     * 命令行输出
     * @param $text
     */
    public function cliErrorOut($text)
    {
        if($this->request->getOs() == 'linux') {
            printf("\033[31;31m%s\033[0m", $text.PHP_EOL);

        } else {
            echo $text;
        }

        exit();
    }

}
