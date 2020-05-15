<?php
// +----------------------------------------------------------------------
// | zibi [ WE CAN DO IT MORE SIMPLE]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2019 http://xmzibi.com All rights reserved.
// +----------------------------------------------------------------------
// | Author：MrYe       <email：55585190@qq.com>
// +----------------------------------------------------------------------
namespace og\cache;

class Cache
{
    /**
     * 设置缓存
     * @param $key
     * @param $data
     * @param int $expire
     * @return array|bool|Memcache|Redis
     */
    public function set($key, $data, $expire = 0)
    {
        if($data === null) {
            //删除缓存信息
            return $this->del($key);

        }

        return cache_write($key, $data, $expire);
    }

    /**
     * 获取缓存
     * @param $key
     * @return array|bool|Memcache|mixed|Redis|string
     */
    public function get($key)
    {
        return cache_read($key);
    }

    /**
     * 删除缓存
     * @param $key
     * @return array|bool
     */
    public function del($key)
    {
        return cache_delete($key);
    }
}