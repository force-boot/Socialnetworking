<?php

namespace app\common\controller;

use think\Controller;

/**
 * 基础控制器类
 * @package app\common\controller
 * @author XieJiaWei<print_f@hotmail.com>
 * @version 1.0.0
 */
class BaseController extends Controller
{
    /**
     * Api统一返回格式
     * @param string $msg
     * @param array $data
     * @param int $code
     * @return \think\response\Json
     */
    public static function showResCode(string $msg = '未知', array $data = [], int $code = 200)
    {
        $res = [
            'msg' => $msg,
            'data' => $data
        ];
        return json($res, $code);
    }


    /**
     * Api统一返回格式 无数据
     * @param string $msg
     * @param int $code
     * @return \think\response\Json
     */
    public static function showResCodeWithOutData(string $msg = '未知', int $code = 200)
    {
        return self::showResCode($msg, [], $code);
    }
}
