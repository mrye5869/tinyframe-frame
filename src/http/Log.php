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


class Log
{

    /**
     * 应用对象
     * @var App
     */
    protected $app;

    /**
     * 请求对象
     * @var Request
     */
    protected $request;

    /**
     * 应用对象
     * @var Config
     */
    protected $config;


    /**
     * Log constructor.
     * @param App $app
     * @param Config $config
     * @param WeAccount $weAccount
     */
    public function __construct(App $app, Request $request, Config $config)
    {
        $this->app = $app;
        $this->request = $request;
        $this->config = $config;
    }

    /**
     * 日志写入
     *
     * @param $name
     * @param $arguments
     * @return bool|mixed|string
     */
    public function __call($name, $arguments)
    {
        // TODO: Implement __call() method.
        if (!empty($arguments)) {
            return log_write($arguments, $name);
        }

        return false;
    }

    /**
     * 写入日志
     *
     * @param string $log 日志信息
     * @return bool|int|void
     */
    public function save($log)
    {
        return $this->write($log, '/');
    }

    /**
     * 用户自定义记录的错误
     *
     * @param $msg              提示信息
     * @param array $data 数据
     * @param string $uniacid 公众号id
     * @param string $openid 用户openid
     * @return bool|int|void
     */
    public function userError($msg, $data = [], $uniacid = '', $openid = '')
    {
        if (empty($msg) || !is_string($msg)) {
            return false;
        }
        $debug = [];
        if (!empty($data['file']) && !empty($data['line'])) {
            $debug = $data;
        } else {
            $debug = [
                'method' => $this->request->method(),
                'url' => $this->request->getUrl(),
                'msg' => $msg,
                'data' => $data,
            ];
        }
        $tipsArr = [];
        foreach ($debug as $key => $val) {
            if (is_array($val) || is_object($val)) {
                $val = var_export($val, true);
            }
            $tipsArr[] = $key . '：' . $val;
        }
        $tips = implode($this->config->get('app.log.delimiter'), $tipsArr);
        //发送微信消息
        if ($uniacid && $openid && class_exists('WeAccount')) {
            $accountApi = \WeAccount::create($uniacid);
            $accountApi->sendCustomNotice(array(
                'touser' => $openid,
                'msgtype' => 'text',
                'text' => array(
                    'content' => urlencode($tips),
                ),
            ));
        }

        return $this->write($tips, 'user_error/');
    }

    /**
     * 记录系统错误信息
     *
     * @param $msg 错误信息
     * @return bool
     */
    public function error($msg)
    {
        if (empty($msg)) {
            return false;
        }
        $debug = [
            'method' => $this->request->method(),
            'url' => $this->request->getUrl(),
            'body' => $this->request->input(),
        ];
        if (is_array($msg) && !empty($msg['file']) && !empty($msg['line'])) {
            $debug = array_merge($debug, $msg);
        } else if (!empty($msg)) {
            //查找代码调用的真正行数
            $debug = array_merge($debug, [
                'msg' => $msg,
            ]);
        }
        $tipsArr = [];
        foreach ($debug as $key => $val) {
            if (is_array($val) || is_object($val)) {
                $val = var_export($val, true);
            }
            $tipsArr[] = $key . '：' . $val;
        }
        $tips = implode($this->config->get('app.log.delimiter'), $tipsArr);

        return $this->write($tips, 'error/');
    }

    /**
     * 写日志
     * @param $log
     * @param string $filename
     * @return bool|int|void
     */
    protected function write($log, $filename = 'run')
    {
        $logRootPath = $this->app->getRootPath() . $this->config->get('app.log.root_path', 'data/logs/');
        $filename = $logRootPath . $filename . date('Y-m-d') . '.log';
        if (is_file($filename)) {
            $size = filesize($filename) / (1024 * 1024);
            $size = ceil($size);
            if ($size > $this->config->get('app.log.max_size', 2)) {
                $filename = $logRootPath . $filename . '_' . date('Ymd') . '-' . date('m') . '.log';
            }
        }
        og_mkdirs(dirname($filename));
        if (is_array($log) || is_object($log)) {
            $log = var_export($log, true);
        }
        $character = $this->config->get('app.log.delimiter');
        $logArr = [
            'dateTime：' . date('Y-m-d H:i:s'),
            $character,
            $log,
            $character,
            $this->config->get('app.log.end_delimiter'),
            $character,
        ];

        return @file_put_contents($filename, implode('', $logArr), FILE_APPEND);
    }

}