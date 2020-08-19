<?php

namespace app\http\middleware;

/**
 * Class ApiUserAuth
 * @package app\http\middleware
 * @author XieJiaWei<print_f@hotmail.com>
 * @version 1.0.0
 */
class ApiUserAuth
{
    /**
     * @param $request
     * @param \Closure $next
     * @return mixed
     * @throws \app\lib\exception\BaseException
     */
    public function handle($request, \Closure $next)
    {
        //获取头部信息
        $param = $request->header();
        //不含token
        if (!array_key_exists('token', $param)) ApiException('非法token，禁止操作', 20003, 200);
        // 当前用户 是否登录
        $token = $param['token'];
        $user = cache($token);
        // 未登录或 已过期
        if (!$user) ApiException('非法token，请重新登录', 20003, 200);
        $request->userToken = $token;
        $request->userId = array_key_exists('type', $user) ? $user['user_id'] : $user['id'];
        $request->userTokenUserInfo = $user;
        return $next($request);
    }
}
