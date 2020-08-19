<?php

namespace app\http\middleware;

use app\common\model\User;

/**
 * Class ApiUserStatus
 * @package app\http\middleware
 * @author XieJiaWei<print_f@hotmail.com>
 * @version 1.0.0
 */
class ApiUserStatus
{
    /**
     * @param $request
     * @param \Closure $next
     * @return mixed
     * @throws \app\lib\exception\BaseException
     */
    public function handle($request, \Closure $next)
    {
        $param = $request->userTokenUserInfo;
        (new User())->checkStatus($param, true);

        return $next($request);
    }
}
