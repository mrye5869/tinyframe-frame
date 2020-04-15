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

use og\http\Request;
use og\http\Response;

class Redirect extends Response
{
    /**
     * 请求对象
     * @var Request
     */
    protected $request;

    public function __construct(Request $request, $data = '', $code = 200)
    {
        $this->request = $request;
        $this->init($data, $code);
    }

    /**
     * 重定向
     * @param $url
     */
    protected function output($url)
    {
        $this->header('Location', $url);

        return '';
    }

}