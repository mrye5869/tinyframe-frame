<?php
// +----------------------------------------------------------------------
// | zibi [ WE CAN DO IT MORE SIMPLE]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2020 http://xmzibi.com/ All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: mrye
// +----------------------------------------------------------------------
namespace og\session;

class Session
{

    /**
     * 设置session
     *
     * @param String $name   session name
     * @param Mixed  $data   session data
     * @param Int    $expire 超时时间(秒)
     */
    public static function set($name, $data, $expire = 0)
    {
        self::start();
        if($expire == 0) {
            //无时间限制
            $_SESSION[$name] = $data;
        } elseif($data === null) {
            //清除session
            self::clear($name);
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
    public static function get($name)
    {
        self::start();
        if(isset($_SESSION[$name])) {
            if (!empty($_SESSION[$name]['expire'])) {
                if ($_SESSION[$name]['expire'] > time()) {
                    return $_SESSION[$name]['data'];
                } else {
                    self::clear($name);
                }
            } else {
                return $_SESSION[$name];
            }
        }

        return null;
    }

    /**
     * 开启session
     */
    private static function start()
    {
        if (PHP_SESSION_ACTIVE != session_status())
        {
            session_start();
        }
    }

    /**
     * 清除session
     *
     * @param $name
     */
    private static function clear($name)
    {
        unset($_SESSION[$name]);
    }

}