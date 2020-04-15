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
namespace og\http\response;

use og\http\Response;

/**
 * Json Response
 */
class Json extends Response
{
    // 输出参数
    protected $options = [
        'json_encode_param' => JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT,
    ];

    protected $contentType = 'application/json';

    public function __construct($data = '', $code = 200)
    {
        $this->init($data, $code);
    }

    /**
     * 设置options
     *
     * @param array $options
     * @return $this
     */
    public function options($options)
    {
        if(!empty($options) && is_array($options)) {
            $this->options = array_merge($this->options, $options);
        }

        return $this;
    }

    /**
     * 处理数据
     * @access protected
     * @param  mixed $data 要处理的数据
     * @return string
     * @throws \Exception
     */
    protected function output($data)
    {

        if(is_string($data)) {
            //字符串直接返回
            return $data;
        }
        try {
            // 返回JSON数据格式到客户端 包含状态信息
            $data = json_encode($data, $this->options['json_encode_param']);

            if (false === $data) {
                throw new \InvalidArgumentException(json_last_error_msg());
            }

            return $data;
        } catch (\Exception $e) {
            if ($e->getPrevious()) {
                throw $e->getPrevious();
            }
            throw $e;
        }
    }

}
