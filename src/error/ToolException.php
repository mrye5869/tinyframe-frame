<?php
// +----------------------------------------------------------------------
// | zibi [ WE CAN DO IT MORE SIMPLE]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2020 http://xmzibi.com/ All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: mrye  55585190@qq.com
// +----------------------------------------------------------------------
namespace og\error;

use Exception;
class ToolException extends Exception
{
    /**
     * 抛出异常的语言包
     *
     * @var array
     */
    protected static $langs = [
        'The phone number is not correct'   => '手机号码格式错误',
        'File not selected for upload'  => '未选择上传的文件',
        'This file type is not allowed to be uploaded'  => '不允许上传此文件类型',
        'background is not null'    => '背景图片不能为空',
        'Background image path error'   => '背景图片路径错误',
        'Image formatting error or Imagick extension not enabled'   => '图片格式错误或未开启 Imagick 扩展',
        'The payment function is only available on the phone'   => '支付功能只能在手机上使用',
        'This order has been paid successfully, so there is no need to pay again'   => '这个订单已经支付成功, 不需要重新支付',
        'There is no valid payment method, please contact the webmaster'    => '没有有效的支付方式, 请联系网站管理员',
        'Not configured timely to the account'  => '未配置及时及时到账',
        'error response'  => '当前请求无响应',
        'There is no exportable data'   => '没有可导出的数据',
        'The exported field is incorrect'   => '导出的字段有误',
        'The file read does not exist'  => '读取的文件不存在',

    ];

    /**
     * 初始化
     *
     * ToolException constructor.
     * @param $statusCode
     * @param null $message
     * @param Exception|null $previous
     * @param array $headers
     * @param int $code
     */
    public function __construct($message = "", $code = 0, \Throwable $previous = null)
    {
        parent::__construct(self::getMsg($message), $code, $previous);
    }

    /**
     * 获取Exception的实际提示信息
     *
     * @param null $message
     * @return bool|mixed|null
     */
    protected static function getMsg($message = null)
    {
        if(empty($message)) {
            return false;
        }

        return isset(self::$langs[$message]) ? self::$langs[$message] : $message;
    }
}