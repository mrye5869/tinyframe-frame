<?php
// +----------------------------------------------------------------------
// | zibi [ WE CAN DO IT MORE SIMPLE]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2019 http://xmzibi.com All rights reserved.
// +----------------------------------------------------------------------
// | Author：MrYe       <email：55585190@qq.com>
// +----------------------------------------------------------------------
namespace og\session;

class Session
{
    /**
     * Session constructor.
     */
    public function __construct()
    {
        $this->start();
    }

    /**
     * 设置session
     *
     * @param String $name   session name
     * @param Mixed  $data   session data
     * @param Int    $expire 超时时间(秒)
     */
    public function set($name, $data, $expire = 0)
    {

        if($expire == 0) {
            //无时间限制
            $_SESSION[$name] = $data;

        } elseif($data === null) {
            //清除session
            $this->clear($name);

        } else {
            //有时间限制
            $session_data = array();
            $session_data['data'] = $data;
            $session_data['expire'] = time() + $expire;
            $_SESSION[$name] = $session_data;

        }

        return true;
    }


    /**
     * 读取session
     *
     * @param $name
     * @return null
     */
    public function get($name)
    {

        if(isset($_SESSION[$name])) {
            //存在
            if (!empty($_SESSION[$name]['expire'])) {
                if ($_SESSION[$name]['expire'] > time()) {
                    return $_SESSION[$name]['data'];
                } else {
                   $this->clear($name);
                }

            } else {
                return $_SESSION[$name];

            }
        }

        return null;
    }

    /**
     * session开启
     */
    protected function start()
    {
        if (PHP_SESSION_ACTIVE != session_status()) {

            return session_start();
        }

        return true;
    }

    /**
     * 清除session
     *
     * @param $name
     */
    protected function clear($name)
    {
        unset($_SESSION[$name]);
    }

}