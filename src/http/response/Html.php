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
namespace og\http\response;

use og\http\Response;


/**
 * Html Response
 */
class Html extends Response
{
    /**
     * 输出type
     * @var string
     */
    protected $contentType = 'text/html';

    public function __construct($data = '', $code = 200)
    {
        $this->init($data, $code);
    }

    /**
     * @param mixed $data
     * @return mixed
     */
    protected function output($data)
    {
        return $data;
    }
}
