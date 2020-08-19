<?php

namespace app\lib\exception;

use Exception;
use think\exception\Handle;

/**
 * 全局异常类
 * @package app\lib\exception
 * @author XieJiaWei<print_f@hotmail.com>
 * @version 1.0.0
 */
class ExceptionHandler extends Handle
{
    /**
     * @var int 状态码
     */
    public $code;

    /**
     * @var string 错误信息
     */
    public $msg;

    /**
     * @var int 错误状态码
     */
    public $errorCode;

    /**
     * @param Exception $e
     * @return \think\Response|\think\response\Json
     */
    public function render(Exception $e)
    {
        if ($e instanceof BaseException) {
            $this->code = $e->code;
            $this->msg = $e->msg;
            $this->errorCode = $e->errorCode;
        }else{
            //调试模式下 输出框架默认错误提示
            if (config('app.app_debug')) return parent::render($e);
            $this->code = 500;
            $this->msg = '服务器异常';
            $this->errorCode = 999;
        }
        return json([
            'msg' => $this->msg,
            'errorCode' => $this->errorCode
        ], $this->code);
    }
}