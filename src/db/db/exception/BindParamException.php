<?php
// +----------------------------------------------------------------------
// | zibi [ WE CAN DO IT MORE SIMPLE]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2020 http://xmzibi.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: MrYe <55585190@qq.com>
// +----------------------------------------------------------------------

namespace og\db\db\exception;

/**
 * PDO参数绑定异常
 */
class BindParamException extends DbException
{

    /**
     * BindParamException constructor.
     * @param string $message
     * @param array  $config
     * @param string $sql
     * @param array    $bind
     * @param int    $code
     */
    public function __construct($message, $config, $sql, $bind, $code = 10502)
    {
        $this->setData('Bind Param', $bind);
        parent::__construct($message, $config, $sql, $code);
    }
}
