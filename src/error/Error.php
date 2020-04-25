<?php
// +----------------------------------------------------------------------
// | zibi [ WE CAN DO IT MORE SIMPLE]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2020 http://xmzibi.com/ All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: MrYe  <55585190@qq.com>
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

    /**
     * request对象
     * @var Request
     */
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
    }

    /**
     * 注册异常处理
     *
     * @access public
     * @return void
     */
    public function register()
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

        $this->showError($e);
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

        try {
           throw new \Exception($errstr, $errno);

        } catch (\Exception $exception) {

            $this->showError($exception);
        }
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
     * @param \Exception|\Throwable $exception
     * @return array
     */
    protected function  convertException($exception)
    {
        //获取调试模式错误信息
        $traces = [];
        $nextException = $exception;
        do {
            $traces[] = [
                'name'    => get_class($nextException),
                'file'    => $nextException->getFile(),
                'line'    => $nextException->getLine(),
                'code'    => $nextException->getCode(),
                'message' => $nextException->getMessage(),
                'trace'   => $nextException->getTrace(),
            ];
        } while ($nextException = $nextException->getPrevious());
        $data = [
            'code'    => $exception->getCode(),
            'message' => $exception->getMessage(),
            'traces'  => $traces,
            'datas'   => [],
            'tables'  => [
                'GPC Data'              => $this->request->input(),
                'W'                     => $this->request->_W(),
            ],
        ];

        return $data;
    }

    /**
     * 展示错误信息
     * @param \Exception|\Throwable $exception
     */
    protected function showError($exception)
    {

        if(is_callable([$exception, 'getStatusCode'])) {
            $httpCode =   $exception->getStatusCode();

        } else {

            $httpCode = 500;
        }
        http_response_code($httpCode);
        
        if($this->app->isDebug()) {
            //调试模式
            if(PHP_SAPI == 'cli') {
                //cli模式
                $br = PHP_EOL;
                $tips = <<<EOF
    {$br}
    message:{$exception->getMessage()}
    file:{$exception->getFile()}
    line:{$exception->getLine()}
    {$br}
EOF;
                //获取命令行标准输出
                $content = $this->cliErrorOut($tips);

            } else {
                //普通模式
                $data = $this->convertException($exception);

                $content = Response::create('view', 'View', $httpCode)->assign($data)->viewCompile(
                    __DIR__.'/view/exception.html',
                    $this->app->getRootPath() ."data/tpl/common/".$this->app->getModuleName()."/404.exception.tpl.php");

            }

        } else {

            //普通模式
            $data = [
                'code'      => $exception->getCode(),
                'message'   => $exception->getMessage(),
                'line'      => $exception->getLine(),
            ];
            //写日志
            $this->log->error($data);
            //模板
            $tpl = $this->app->config->get('app.error.show_error_tpl');
            if(empty($tpl) || !is_file($tpl)) {
                $tpl =  __DIR__.'/view/404.html';
            }
            //错误信息
            $data['message'] = $this->app->config->get('app.error.show_error_msg', $data['message']);
            $content = Response::create('view', 'View', $httpCode)->assign($data)->viewCompile(
                $tpl,
                $this->app->getRootPath() ."data/tpl/common/".$this->app->getModuleName()."/404.tpl.php");

        }

        echo $content;
        exit();

    }

    /**
     * 命令行输出
     * @param $text
     */
    public function cliErrorOut($text)
    {

        if($this->request->getOs() == 'linux') {
            $echo = sprintf("\033[31;31m%s\033[0m", $text);

        } else {

            $echo = $text;
        }

        return $echo.PHP_EOL;
    }

}
