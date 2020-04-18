<?php
// +----------------------------------------------------------------------
// | zibi [ WE CAN DO IT MORE SIMPLE]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2019 http://xmzibi.com All rights reserved.
// +----------------------------------------------------------------------
// | Author：MrYe       <email：55585190@qq.com>
// +----------------------------------------------------------------------
namespace og\cookie;

class Cookie
{

    /**
     * 设置cookie
     * @param $key
     * @param $value
     * @param int $expire
     * @param bool $httponly
     * @return bool
     */
    public function set($key, $value, $expire = 0, $httponly = false)
    {
        return isetcookie($key, $value, $expire, $httponly);
    }

    /**
     * 获取cookie
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        $data = igetcookie($key);
        try {
            // 返回JSON数据
            $data = json_decode($data, true);

            return $data;
        } catch (\Exception $e) {

            return $data;
        }
    }
}