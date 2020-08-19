<?php

namespace app\http\middleware;

/**
 * Class ApiUserBindPhone
 * @package app\http\middleware
 * @author XieJiaWei<print_f@hotmail.com>
 * @version 1.0.0
 */
class ApiUserBind
{
    /**
     * @param $request
     * @param \Closure $next
     * @return mixed
     * @throws \app\lib\exception\BaseException
     */
    public function handle($request, \Closure $next)
    {
        if ($request->userId < 1) ApiException('请先完善资料', 20008, 200);
        return $next($request);
    }
}
