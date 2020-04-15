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
namespace og\facade;


use og\http\Facade;
/**
 * @see \og\http\Log
 * @mixin \og\http\Log
 */
class Log extends Facade
{

    protected static function getFacadeClass()
    {
        return 'og\http\Log';
    }
}