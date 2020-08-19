<?php


namespace app\http\middleware;

/**
 * Class ApiGetUserId
 * @package app\http\middleware
 * @author XieJiaWei<print_f@hotmail.com>
 * @version 1.0.0
 */
class ApiGetUserId
{
    /**
     * @param $request
     * @param \Closure $next
     * @return mixed
     * @throws \app\lib\exception\BaseException
     */
    public function handle($request, \Closure $next)
    {
        // 获取头部信息
        $param = $request->header();
        if (array_key_exists('token', $param)) {
            if ($user = cache($param['token'])) {
                $request->userId = array_key_exists('type', $user) ? $user['user_id'] : $user['id'];
            }
        }
        return $next($request);
    }
}