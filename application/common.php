<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

use app\lib\exception\BaseException;
use \think\facade\Cache;

/**
 * 异常类输出
 * @param string $msg 错误信息
 * @param int $errorCode 错误代码
 * @param int $code 状态码
 * @throws BaseException
 */
function ApiException($msg = '接口异常', $errorCode = 999, $code = 400)
{
    throw new BaseException([
        'code' => $code,
        'msg' => $msg,
        'errorCode' => $errorCode
    ]);
}

/**
 * 生成随机浮点数
 * @param int $min 最小值
 * @param int $max 最大值
 * @param int $digit 保留小数位数
 * @return float|int
 */
function randomFloat(int $min = 0, int $max = 1, int $digit = 0)
{
    $resFloat = $min + mt_rand() / mt_getrandmax() * ($max - $min);
    return !$digit ? $resFloat : round($resFloat, $digit);
}

/**
 * 多维数组通过键 进行数组排序
 * @param array $array 排序的数组
 * @param $key mixed 用来排序的键名
 * @param string $type 排序类型 大小写不敏感 desc or asc
 * @return array
 */
function multiArraySort(array $array, $key, string $type = 'asc'): array
{
    // 判断排序类型
    $sortType = strtolower($type) == 'asc' ? SORT_ASC : SORT_DESC;

    foreach ($array as $row_array) {
        if (!is_array($row_array)) return [];

        $key_array[] = $row_array[$key];
    }

    if (!array_multisort($key_array, $sortType, $array)) return [];

    return $array;
}

/**
 * 三维数组转换二维数组
 * @param array $array
 * @return array
 */
function threeArrayToTwo(array $array)
{
    $temp = [];
    foreach ($array as $key => $val) {
        $temp = $val;
    }
    return $temp;
}

if (!function_exists('redis')) {
    /**
     * 获取redis操作句柄
     * @return object|Redis
     */
    function redis()
    {
        return Cache::store('redis')->handler();
    }
}

/**
 * 获取IP所在地 省-市
 * @param string $ip ip地址 为空获取当前请求IP
 * @return string
 */
function getIpCity(string $ip = '')
{
    if (empty($ip)) $ip = request()->ip();
    $url = 'http://whois.pconline.com.cn/ipJson.jsp?json=true&ip=';
    $city = file_get_contents($url . $ip);
    $city = mb_convert_encoding($city, "UTF-8", "GB2312");
    $city = json_decode($city, true);
    return $city['pro'] . '-' . $city['city'];
}

/**
 * 生成唯一key
 * @param string $param 附加参数
 * @return string
 */
function createUniqueKey(string $param = 'token'): string
{
    $md5 = md5(uniqid(md5(microtime(true)), true));
    return sha1($md5 . md5($param));
}

/**
 * 字符串转义
 * @param $string
 * @param int $force
 * @param bool $strip
 * @return array|string
 */
function daddslashes($string, $force = 0, $strip = FALSE)
{
    !defined('MAGIC_QUOTES_GPC') && define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
    if (!MAGIC_QUOTES_GPC || $force) {
        if (is_array($string)) {
            foreach ($string as $key => $val) {
                $string[$key] = daddslashes($val, $force, $strip);
            }
        } else {
            $string = addslashes($strip ? stripslashes($string) : $string);
        }
    }
    return $string;
}

/**
 * 获取文件完整url
 * @param string $url
 * @param string|bool
 * @return string|void
 */
function getFileUrl($url = '', $domain = true)
{
    if (!$url) return;
    return url($url, '', false, $domain);
}