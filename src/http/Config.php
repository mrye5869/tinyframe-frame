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
namespace og\http;

use og\Loader;
class Config
{
    /**
     * app对象
     *
     * @var null|App
     */
    protected $app = null;

    /**
     * 配置数据
     *
     * @var array
     */
    protected $data = [];

    /**
     * 初始化
     *
     * Config constructor.
     * @param App $app
     * @param array $config
     */
    public function __construct(App $app, $config = [])
    {
        $this->app = $app;
        $this->loadDefault($config);
    }

    /**
     * 获取配置参数
     *
     * @param string $name
     * @param null $default
     * @return array|mixed|null
     */
    public function get($name = '', $default = null)
    {
        if (empty($name)) {
            return $this->data;
        }
        $name = explode('.', $name);
        $value = $this->data;
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
     * 加载配置文件
     *
     * @param array $config
     */
    protected function loadDefault($config = [])
    {
        $this->data = $config;
        //加载配置文件
        $list = Loader::listFile($this->app->getModulePath().'config');
        foreach ($list as $key => $files) {
            if($files['isFile'] && $files['ext'] == 'php') {
                $this->data[$files['name']] = Loader::_include($files['pathname']);
            }
        }

        return $this->data;
    }


}